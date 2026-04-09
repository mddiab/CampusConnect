@extends('layouts.app')

@section('title', 'CampusConnect Login')

@section('content')
    <main class="auth-shell">
        <div class="auth-grid">
            <section class="auth-hero">
                <p class="eyebrow">CampusConnect</p>
                <h1>Campus support, with less confusion and a clearer path.</h1>
                <p>
                    Sign in once and land in the right workspace immediately. Students can submit requests,
                    staff can review and update them, and admins can manage the whole system from one place.
                </p>

                <ul>
                    <li>Students get a simple request form, request history, and clear status updates.</li>
                    <li>Staff get a filtered queue for their department with notes and progress controls.</li>
                    <li>Admins get user management, category management, and reporting tools.</li>
                </ul>
            </section>

            <section class="auth-card">
                <h2>Welcome Back</h2>
                <p>Use a seeded demo account or your own campus credentials to enter the correct dashboard.</p>

                @if ($errors->any())
                    <div class="error-box">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Enter the user's university email address here"
                            required
                            autofocus
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="Enter the user's account password here"
                            required
                        >
                    </div>

                    <div class="remember-row">
                        <div class="checkbox-wrap">
                            <input id="remember" type="checkbox" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                    </div>

                    <button type="submit" class="button button-primary" style="width: 100%;">Login to Dashboard</button>
                </form>

                <div class="demo-box">
                    <h3>Demo Accounts</h3>
                    <p><strong>Student sample:</strong> student@campusconnect.test / password</p>
                    <p><strong>Staff sample:</strong> staff@campusconnect.test / password</p>
                    <p><strong>Admin sample:</strong> admin@campusconnect.test / password</p>
                    <p>Additional seeded users include student2-student10, admin2-admin3, and one staff account for every department.</p>
                </div>
            </section>
        </div>
    </main>
@endsection
