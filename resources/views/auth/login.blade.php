@extends('layouts.app')

@section('title', 'CampusConnect Login')

@section('content')
    <main class="auth-shell">
        <div class="auth-grid auth-grid-compact">
            <section class="auth-card auth-card-minimal">
                <div class="auth-card-intro">
                    <p class="eyebrow">CampusConnect</p>
                    <h1>Login</h1>
                </div>

                @if ($errors->any())
                    <div class="error-box">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="auth-form">
                    @csrf

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="name@campus.edu"
                            required
                            autofocus
                            autocomplete="username"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >
                    </div>

                    <div class="remember-row">
                        <label for="remember" class="checkbox-wrap">
                            <input id="remember" type="checkbox" name="remember">
                            <span class="checkbox-indicator" aria-hidden="true"></span>
                            <span class="checkbox-text">Remember me</span>
                        </label>
                    </div>

                    <button type="submit" class="button button-primary auth-submit">Login</button>
                </form>

                @if (app()->environment(['local', 'testing']))
                    <details class="auth-devnote">
                        <summary>Local Demo Accounts</summary>
                        <div class="auth-devnote-body">
                            <p><strong>Student:</strong> student@campusconnect.test / password</p>
                            <p><strong>Staff:</strong> staff@campusconnect.test / password</p>
                            <p><strong>Admin:</strong> admin@campusconnect.test / password</p>
                        </div>
                    </details>
                @endif
            </section>
        </div>
    </main>
@endsection
