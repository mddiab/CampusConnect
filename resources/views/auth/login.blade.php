@extends('layouts.app')

@section('title', 'CampusConnect Login')

@section('content')
    <main class="auth-shell">
        <div class="auth-grid">
            <section class="auth-hero">
                <p style="margin: 0 0 14px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase;">CampusConnect</p>
                <h1>Login first. Then send each user to the right space.</h1>
                <p>
                    This first version starts with a simple login page for students, staff, and administrators.
                    Once a user signs in, CampusConnect redirects them to their own dashboard.
                </p>

                <ul>
                    <li>Students can submit and track service requests.</li>
                    <li>Staff can review requests and update progress.</li>
                    <li>Admins can monitor users, departments, and overall activity.</li>
                </ul>
            </section>

            <section class="auth-card">
                <h2>Login</h2>
                <p>Use your account credentials to enter CampusConnect.</p>

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
                    <p><strong>Student:</strong> student@campusconnect.test / password</p>
                    <p><strong>Staff:</strong> staff@campusconnect.test / password</p>
                    <p><strong>Admin:</strong> admin@campusconnect.test / password</p>
                </div>
            </section>
        </div>
    </main>
@endsection
