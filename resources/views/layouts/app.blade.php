<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <title>@yield('title', 'CampusConnect')</title>

        <style>
            :root {
                --bg: #eff4fc;
                --panel: #ffffff;
                --panel-soft: #f7faff;
                --primary: #3055b2;
                --primary-dark: #243f87;
                --text: #22304a;
                --muted: #64748f;
                --border: #d7deef;
                --danger: #b94d57;
                --success: #5e8a67;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                font-family: "Trebuchet MS", "Segoe UI", sans-serif;
                color: var(--text);
                background:
                    radial-gradient(circle at top left, rgba(48, 85, 178, 0.12), transparent 30%),
                    linear-gradient(180deg, #f9fbff 0%, var(--bg) 100%);
            }

            a {
                color: inherit;
                text-decoration: none;
            }

            .container {
                width: min(1160px, calc(100% - 40px));
                margin: 0 auto;
            }

            .site-header {
                padding: 22px 0;
            }

            .header-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
            }

            .brand {
                color: var(--primary);
                font-size: 1.6rem;
                font-weight: 700;
            }

            .header-meta {
                display: flex;
                align-items: center;
                gap: 12px;
                flex-wrap: wrap;
            }

            .role-badge {
                display: inline-flex;
                align-items: center;
                min-height: 36px;
                padding: 0 12px;
                border-radius: 999px;
                background: #e8efff;
                color: var(--primary);
                font-size: 0.9rem;
                font-weight: 700;
                text-transform: capitalize;
            }

            .button,
            button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 44px;
                padding: 0 18px;
                border: 0;
                border-radius: 12px;
                cursor: pointer;
                font: inherit;
                font-weight: 700;
            }

            .button-primary {
                background: var(--primary);
                color: #ffffff;
            }

            .button-secondary {
                background: #eaf0fe;
                color: var(--primary-dark);
            }

            .button-plain {
                background: #ffffff;
                color: var(--primary-dark);
                border: 1px solid var(--border);
            }

            .page {
                padding: 18px 0 48px;
            }

            .hero-card,
            .panel,
            .mini-card {
                background: var(--panel);
                border: 1px solid var(--border);
                border-radius: 22px;
                box-shadow: 0 14px 30px rgba(34, 56, 112, 0.07);
            }

            .hero-card {
                padding: 32px;
                margin-bottom: 20px;
            }

            .hero-card h1 {
                margin: 0 0 12px;
                color: var(--primary);
                font-size: 2.4rem;
            }

            .hero-card p {
                margin: 0;
                max-width: 720px;
                color: var(--muted);
                font-size: 1.05rem;
                line-height: 1.7;
            }

            .page-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 18px;
                align-items: start;
            }

            .panel-grid {
                display: grid;
                grid-template-columns: minmax(0, 1.45fr) minmax(280px, 0.9fr);
                gap: 18px;
                align-items: start;
            }

            .mini-card {
                height: 100%;
                padding: 24px;
            }

            .mini-card h2 {
                margin: 0 0 10px;
                color: var(--primary-dark);
                font-size: 1.2rem;
            }

            .mini-card p,
            .mini-card li,
            .panel p,
            .panel li,
            .panel td,
            .panel th,
            label,
            input,
            select,
            textarea {
                color: var(--muted);
                line-height: 1.6;
                font-size: 1rem;
            }

            .mini-card ul,
            .panel ul {
                margin: 0;
                padding-left: 20px;
            }

            .mini-card ul li + li,
            .panel ul li + li {
                margin-top: 8px;
            }

            .panel {
                padding: 24px;
            }

            .panel h2 {
                margin: 0 0 14px;
                color: var(--primary);
                font-size: 1.45rem;
            }

            .panel-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
                margin-bottom: 14px;
            }

            .panel-header h2 {
                margin: 0;
            }

            .panel-header span {
                color: var(--muted);
                font-size: 0.95rem;
                font-weight: 700;
            }

            .section-stack {
                display: grid;
                gap: 18px;
            }

            .stat-row {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 18px;
                margin-top: 22px;
            }

            .stat-box {
                padding: 20px;
                border-radius: 20px;
                background: var(--panel-soft);
                border: 1px solid var(--border);
            }

            .stat-box strong {
                display: block;
                margin-bottom: 6px;
                color: var(--primary);
                font-size: 2rem;
            }

            .placeholder-value {
                font-size: 1rem;
                line-height: 1.5;
            }

            .placeholder-copy {
                color: var(--muted);
                line-height: 1.7;
            }

            .auth-shell {
                min-height: 100vh;
                display: grid;
                place-items: center;
                padding: 28px 16px;
            }

            .auth-grid {
                width: min(1100px, 100%);
                display: grid;
                grid-template-columns: 1.1fr 0.9fr;
                gap: 22px;
            }

            .auth-hero {
                padding: 42px 34px;
                border-radius: 26px;
                background: linear-gradient(135deg, #3155b3 0%, #4d72d2 100%);
                color: #ffffff;
                box-shadow: 0 18px 38px rgba(36, 63, 135, 0.25);
            }

            .auth-hero h1 {
                margin: 0 0 14px;
                font-size: 2.9rem;
                line-height: 1.1;
            }

            .auth-hero p,
            .auth-hero li {
                margin: 0;
                color: rgba(255, 255, 255, 0.92);
                line-height: 1.7;
                font-size: 1.02rem;
            }

            .auth-hero ul {
                margin: 20px 0 0;
                padding-left: 20px;
            }

            .auth-card {
                padding: 30px 26px;
                border-radius: 26px;
                background: var(--panel);
                border: 1px solid var(--border);
                box-shadow: 0 18px 38px rgba(34, 56, 112, 0.1);
            }

            .auth-card h2 {
                margin: 0 0 8px;
                color: var(--primary);
                font-size: 2rem;
            }

            .auth-card > p {
                margin: 0 0 22px;
                color: var(--muted);
            }

            .form-group + .form-group {
                margin-top: 16px;
            }

            label {
                display: block;
                margin-bottom: 8px;
                font-weight: 700;
                color: var(--text);
            }

            input[type="email"],
            input[type="password"],
            input[type="text"],
            input[type="file"],
            select,
            textarea {
                width: 100%;
                min-height: 48px;
                padding: 12px 14px;
                border: 1px solid var(--border);
                border-radius: 12px;
                background: #ffffff;
                color: var(--text);
            }

            textarea {
                min-height: 140px;
                resize: vertical;
            }

            input[type="email"],
            input[type="password"],
            input[type="text"] {
                padding: 0 14px;
            }

            input:focus,
            select:focus,
            textarea:focus {
                outline: 2px solid rgba(48, 85, 178, 0.18);
                border-color: var(--primary);
            }

            .remember-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin: 18px 0 22px;
            }

            .checkbox-wrap {
                display: inline-flex;
                align-items: center;
                gap: 10px;
            }

            .checkbox-wrap label {
                margin: 0;
                font-weight: 400;
                color: var(--muted);
            }

            .error-box {
                margin-bottom: 16px;
                padding: 12px 14px;
                border-radius: 12px;
                background: #fff1f2;
                border: 1px solid #f1c7cd;
                color: var(--danger);
            }

            .success-box {
                margin-bottom: 16px;
                padding: 12px 14px;
                border-radius: 12px;
                background: #eef9f0;
                border: 1px solid #c9e6cf;
                color: var(--success);
            }

            .field-help {
                display: block;
                margin-top: 6px;
                color: var(--muted);
                font-size: 0.92rem;
            }

            .form-actions {
                display: flex;
                align-items: center;
                justify-content: flex-start;
                gap: 12px;
                margin-top: 22px;
            }

            .demo-box {
                margin-top: 20px;
                padding: 18px;
                border-radius: 16px;
                background: var(--panel-soft);
                border: 1px solid var(--border);
            }

            .demo-box h3 {
                margin: 0 0 10px;
                color: var(--primary-dark);
                font-size: 1rem;
            }

            .demo-box p {
                margin: 6px 0 0;
                color: var(--muted);
                font-size: 0.95rem;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            .table-wrap {
                overflow-x: auto;
            }

            th,
            td {
                padding: 12px;
                text-align: left;
                border: 1px solid var(--border);
            }

            th {
                background: #3155b3;
                color: #ffffff;
            }

            tbody tr:nth-child(even) {
                background: #f8faff;
            }

            .empty-state {
                padding: 20px;
                border-radius: 16px;
                background: var(--panel-soft);
                border: 1px dashed var(--border);
                color: var(--muted);
                line-height: 1.7;
            }

            .status-badge {
                display: inline-flex;
                align-items: center;
                min-height: 32px;
                padding: 0 10px;
                border-radius: 999px;
                font-size: 0.9rem;
                font-weight: 700;
            }

            .status-pending {
                background: #fff5db;
                color: #8d6510;
            }

            .status-in-progress {
                background: #e7f1ff;
                color: #2354a3;
            }

            .status-completed {
                background: #eaf8ee;
                color: #2f7a4f;
            }

            .text-link {
                color: var(--primary);
                font-weight: 700;
            }

            .text-link:hover {
                color: var(--primary-dark);
            }

            .section-note {
                margin: 0 0 18px;
                color: var(--muted);
                line-height: 1.7;
            }

            .muted-text {
                color: var(--muted);
            }

            @media (max-width: 960px) {
                .auth-grid,
                .panel-grid,
                .page-grid,
                .stat-row {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-width: 720px) {
                .hero-card {
                    padding: 26px 20px;
                }

                .hero-card h1 {
                    font-size: 2rem;
                }

                .auth-hero,
                .auth-card {
                    padding: 28px 20px;
                }

                .auth-hero h1,
                .auth-card h2 {
                    font-size: 2.1rem;
                }

                .header-row {
                    flex-direction: column;
                    align-items: flex-start;
                }
            }
        </style>
    </head>
    <body>
        @auth
            <header class="site-header">
                <div class="container header-row">
                    <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="brand">CampusConnect</a>

                    <div class="header-meta">
                        <span class="role-badge">{{ auth()->user()->role }}</span>
                        <span>{{ auth()->user()->name }}</span>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="button button-plain">Logout</button>
                        </form>
                    </div>
                </div>
            </header>
        @endauth

        @yield('content')
    </body>
</html>
