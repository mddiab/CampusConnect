<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->seedReferenceData();

        $hasServiceCategoryId = Schema::hasColumn('service_requests', 'service_category_id');
        $hasStaffNotes = Schema::hasColumn('service_requests', 'staff_notes');
        $hasResolvedAt = Schema::hasColumn('service_requests', 'resolved_at');

        if (! $hasServiceCategoryId || ! $hasStaffNotes || ! $hasResolvedAt) {
            Schema::table('service_requests', function (Blueprint $table) use ($hasServiceCategoryId, $hasStaffNotes, $hasResolvedAt) {
                if (! $hasServiceCategoryId) {
                    $table->foreignId('service_category_id')
                        ->nullable()
                        ->after('department_id')
                        ->constrained('service_categories')
                        ->nullOnDelete();
                }

                if (! $hasStaffNotes) {
                    $table->text('staff_notes')->nullable()->after('description');
                }

                if (! $hasResolvedAt) {
                    $table->timestamp('resolved_at')->nullable()->after('staff_notes');
                }
            });
        }

        $this->backfillServiceRequests();

        $hasLegacyDepartment = Schema::hasColumn('service_requests', 'department');
        $hasLegacyCategory = Schema::hasColumn('service_requests', 'category');

        if ($hasLegacyDepartment || $hasLegacyCategory) {
            Schema::table('service_requests', function (Blueprint $table) use ($hasLegacyDepartment, $hasLegacyCategory) {
                $columns = [];

                if ($hasLegacyDepartment) {
                    $columns[] = 'department';
                }

                if ($hasLegacyCategory) {
                    $columns[] = 'category';
                }

                $table->dropColumn($columns);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $hasLegacyDepartment = Schema::hasColumn('service_requests', 'department');
        $hasLegacyCategory = Schema::hasColumn('service_requests', 'category');

        if (! $hasLegacyDepartment || ! $hasLegacyCategory) {
            Schema::table('service_requests', function (Blueprint $table) use ($hasLegacyDepartment, $hasLegacyCategory) {
                if (! $hasLegacyDepartment) {
                    $table->string('department')->nullable()->after('title');
                }

                if (! $hasLegacyCategory) {
                    $table->string('category')->nullable()->after('department');
                }
            });
        }

        $this->restoreLegacyRequestColumns();

        $hasServiceCategoryId = Schema::hasColumn('service_requests', 'service_category_id');
        $hasStaffNotes = Schema::hasColumn('service_requests', 'staff_notes');
        $hasResolvedAt = Schema::hasColumn('service_requests', 'resolved_at');

        if ($hasServiceCategoryId || $hasStaffNotes || $hasResolvedAt) {
            Schema::table('service_requests', function (Blueprint $table) use ($hasServiceCategoryId, $hasStaffNotes, $hasResolvedAt) {
                if ($hasServiceCategoryId) {
                    $table->dropConstrainedForeignId('service_category_id');
                }

                if ($hasStaffNotes) {
                    $table->dropColumn('staff_notes');
                }

                if ($hasResolvedAt) {
                    $table->dropColumn('resolved_at');
                }
            });
        }
    }

    private function seedReferenceData(): void
    {
        foreach ($this->structure() as $departmentName => $categories) {
            $departmentId = $this->findOrCreateDepartmentId($departmentName);

            foreach ($categories as $categoryName) {
                $this->findOrCreateCategoryId($departmentId, $categoryName);
            }
        }
    }

    private function backfillServiceRequests(): void
    {
        $hasLegacyDepartment = Schema::hasColumn('service_requests', 'department');
        $hasLegacyCategory = Schema::hasColumn('service_requests', 'category');

        $columns = ['id', 'department_id', 'service_category_id', 'status', 'updated_at'];

        if ($hasLegacyDepartment) {
            $columns[] = 'department';
        }

        if ($hasLegacyCategory) {
            $columns[] = 'category';
        }

        $requests = DB::table('service_requests')->select($columns)->get();

        foreach ($requests as $request) {
            $departmentId = $request->department_id;
            $serviceCategoryId = $request->service_category_id;

            if (! $departmentId && $hasLegacyDepartment && ! empty($request->department)) {
                $departmentId = $this->findOrCreateDepartmentId($request->department);
            }

            if (! $serviceCategoryId && $hasLegacyCategory && ! empty($request->category)) {
                if ($departmentId) {
                    $serviceCategoryId = $this->findOrCreateCategoryId($departmentId, $request->category);
                } else {
                    [$departmentId, $serviceCategoryId] = $this->resolveCategoryWithoutDepartment($request->category);
                }
            }

            if (! $departmentId && $serviceCategoryId) {
                $departmentId = DB::table('service_categories')
                    ->where('id', $serviceCategoryId)
                    ->value('department_id');
            }

            $payload = [
                'department_id' => $departmentId,
                'service_category_id' => $serviceCategoryId,
            ];

            if ($request->status === 'completed') {
                $payload['resolved_at'] = $request->updated_at;
            }

            DB::table('service_requests')
                ->where('id', $request->id)
                ->update($payload);
        }
    }

    private function restoreLegacyRequestColumns(): void
    {
        $requests = DB::table('service_requests')
            ->select('id', 'department_id', 'service_category_id')
            ->get();

        foreach ($requests as $request) {
            $departmentName = null;
            $categoryName = null;

            if ($request->department_id) {
                $departmentName = DB::table('departments')
                    ->where('id', $request->department_id)
                    ->value('name');
            }

            if ($request->service_category_id) {
                $categoryName = DB::table('service_categories')
                    ->where('id', $request->service_category_id)
                    ->value('name');
            }

            DB::table('service_requests')
                ->where('id', $request->id)
                ->update([
                    'department' => $departmentName,
                    'category' => $categoryName,
                ]);
        }
    }

    private function findOrCreateDepartmentId(string $departmentName): int
    {
        $departmentId = DB::table('departments')
            ->where('name', $departmentName)
            ->value('id');

        if ($departmentId) {
            return (int) $departmentId;
        }

        return (int) DB::table('departments')->insertGetId([
            'name' => $departmentName,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function findOrCreateCategoryId(int $departmentId, string $categoryName): int
    {
        $categoryId = DB::table('service_categories')
            ->where('department_id', $departmentId)
            ->where('name', $categoryName)
            ->value('id');

        if ($categoryId) {
            return (int) $categoryId;
        }

        return (int) DB::table('service_categories')->insertGetId([
            'name' => $categoryName,
            'department_id' => $departmentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @return array{0: int|null, 1: int|null}
     */
    private function resolveCategoryWithoutDepartment(string $categoryName): array
    {
        $record = DB::table('service_categories')
            ->where('name', $categoryName)
            ->select('id', 'department_id')
            ->first();

        if (! $record) {
            return [null, null];
        }

        return [(int) $record->department_id, (int) $record->id];
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function structure(): array
    {
        return [
            'Information Technology' => [
                'Technical Support',
                'General Inquiry',
            ],
            'Maintenance' => [
                'Facility Maintenance',
                'General Inquiry',
            ],
            'Registrar' => [
                'Registration',
                'Document Request',
                'General Inquiry',
            ],
            'Finance' => [
                'Payment',
                'General Inquiry',
            ],
            'Library' => [
                'Document Request',
                'General Inquiry',
            ],
            'Student Affairs' => [
                'General Inquiry',
            ],
        ];
    }
};
