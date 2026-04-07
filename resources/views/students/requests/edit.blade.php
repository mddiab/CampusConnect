@extends('layouts.app')

@section('title', 'Edit Request')

@section('content')
    <main class="page">
        <div class="container">
            <!-- Header Section -->
            <section class="hero-card">
                <h1>Edit Request</h1>
                <p>
                    Update the details of your pending request. Note: You can only edit requests that are still pending.
                    Once a staff member starts working on your request, it will no longer be editable.
                </p>

                <div class="stat-row">
                    <!-- Display department (read-only) -->
                    <div class="stat-box">
                        <strong class="placeholder-value">{{ $serviceRequest->department }}</strong>
                        <span>Department (cannot be changed)</span>
                    </div>

                    <!-- Display category (read-only) -->
                    <div class="stat-box">
                        <strong class="placeholder-value">{{ $serviceRequest->category }}</strong>
                        <span>Category (cannot be changed)</span>
                    </div>

                    <!-- Display status -->
                    <div class="stat-box">
                        <strong class="placeholder-value">
                            <span class="status-badge status-pending">
                                {{ $serviceRequest->statusLabel() }}
                            </span>
                        </strong>
                        <span>Current status</span>
                    </div>
                </div>
            </section>

            <!-- Edit Form -->
            <section class="panel-grid">
                <article class="panel">
                    <div class="panel-header">
                        <h2>Update Request Details</h2>
                        <a href="{{ route('student.requests.show', $serviceRequest) }}" class="text-link">Cancel</a>
                    </div>

                    <!-- Form to update request -->
                    <form method="POST" action="{{ route('student.requests.update', $serviceRequest) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <!-- Title Field -->
                        <div class="form-group">
                            <label for="title">Request Title *</label>
                            <input
                                type="text"
                                id="title"
                                name="title"
                                class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title', $serviceRequest->title) }}"
                                required
                                maxlength="255"
                                placeholder="Brief title for your request"
                            />
                            @error('title')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Description Field -->
                        <div class="form-group">
                            <label for="description">Description *</label>
                            <textarea
                                id="description"
                                name="description"
                                class="form-control @error('description') is-invalid @enderror"
                                required
                                maxlength="5000"
                                rows="6"
                                placeholder="Provide detailed information about your request"
                            >{{ old('description', $serviceRequest->description) }}</textarea>
                            <small class="text-muted">Maximum 5000 characters</small>
                            @error('description')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Attachment Field -->
                        <div class="form-group">
                            <label for="attachment">Attachment (Optional)</label>
                            <p class="text-muted small">
                                Allowed formats: JPG, JPEG, PNG, PDF, DOC, DOCX (Max 2MB)
                            </p>

                            <!-- Show current attachment if exists -->
                            @if ($serviceRequest->attachment_path)
                                <div class="attachment-info">
                                    <strong>Current attachment:</strong>
                                    <a href="{{ route('student.requests.attachment', $serviceRequest) }}" class="text-link">
                                        {{ $serviceRequest->attachment_original_name ?? 'attachment file' }}
                                    </a>
                                    <p class="small text-muted">Upload a new file to replace it</p>
                                </div>
                            @endif

                            <input
                                type="file"
                                id="attachment"
                                name="attachment"
                                class="form-control @error('attachment') is-invalid @enderror"
                                accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                            />
                            @error('attachment')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- FEATURE: Priority Levels - Urgent checkbox in edit form -->
                        <div class="form-group">
                            <label style="display: flex; align-items: center; font-weight: normal;">
                                <input 
                                    type="checkbox" 
                                    name="is_urgent" 
                                    value="1"
                                    @checked(old('is_urgent', $serviceRequest->is_urgent))
                                    style="margin-right: 8px; width: 18px; height: 18px; cursor: pointer;"
                                />
                                🚨 <strong style="margin-left: 5px;">Mark this request as URGENT</strong>
                            </label>
                            <span class="field-help">Check this if your request needs immediate attention. Uncheck to remove urgent status.</span>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                Update Request
                            </button>
                            <a href="{{ route('student.requests.show', $serviceRequest) }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </article>

                <!-- Help Section -->
                <article class="panel">
                    <h2>Important Information</h2>
                    <ul>
                        <li><strong>Editable fields:</strong> Title, description, attachment, and urgent status can be modified.</li>
                        <li><strong>Lock after assignment:</strong> Once staff starts working on your request, you won't be able to edit it.</li>
                        <li><strong>Keep organized:</strong> Clear titles and descriptions help staff resolve your request faster.</li>
                        <li><strong>Attachments:</strong> You can replace your current attachment with a new one.</li>
                        <li><strong>Urgent flag:</strong> Use this for time-sensitive requests that need priority handling.</li>
                        <li><strong>Department & Category:</strong> These cannot be changed after submission. Create a new request if needed.</li>
                    </ul>
                </article>
            </section>
        </div>
    </main>
@endsection
