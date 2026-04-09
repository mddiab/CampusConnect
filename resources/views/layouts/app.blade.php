<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <title>@yield('title', 'CampusConnect')</title>

        <style>
            :root {
                --bg-base: #040713;
                --bg-deep: #081125;
                --bg-surface: rgba(9, 15, 38, 0.72);
                --bg-surface-strong: rgba(11, 17, 42, 0.9);
                --bg-surface-soft: rgba(16, 23, 58, 0.72);
                --bg-field: rgba(9, 14, 36, 0.8);
                --line: rgba(147, 181, 255, 0.18);
                --line-strong: rgba(123, 216, 255, 0.38);
                --text: #f4f7ff;
                --muted: #b8c4eb;
                --muted-strong: #d4ddf8;
                --primary: #7bd8ff;
                --primary-strong: #7d7fff;
                --accent: #ff7bc7;
                --success: #7ff8c4;
                --warning: #ffd885;
                --danger: #ff9db0;
                --shadow-soft: 0 24px 70px rgba(0, 0, 0, 0.35);
                --shadow-strong: 0 28px 90px rgba(3, 7, 25, 0.6);
                --radius-xl: 28px;
                --radius-lg: 22px;
                --radius-md: 16px;
                --radius-pill: 999px;
            }

            * {
                box-sizing: border-box;
            }

            html {
                scroll-behavior: smooth;
            }

            body {
                margin: 0;
                min-height: 100vh;
                font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif;
                color: var(--text);
                background:
                    radial-gradient(circle at top, rgba(125, 127, 255, 0.16), transparent 32%),
                    radial-gradient(circle at 10% 15%, rgba(123, 216, 255, 0.12), transparent 24%),
                    radial-gradient(circle at 85% 20%, rgba(255, 123, 199, 0.16), transparent 28%),
                    linear-gradient(180deg, #060914 0%, #091127 42%, #050914 100%);
                overflow-x: hidden;
            }

            body::before,
            body::after {
                content: "";
                position: fixed;
                inset: 0;
                pointer-events: none;
                z-index: -3;
            }

            body::before {
                background-image:
                    radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.85) 0 1.2px, transparent 1.4px),
                    radial-gradient(circle at 75% 12%, rgba(255, 255, 255, 0.75) 0 1.1px, transparent 1.35px),
                    radial-gradient(circle at 62% 65%, rgba(255, 255, 255, 0.55) 0 1px, transparent 1.25px),
                    radial-gradient(circle at 28% 76%, rgba(255, 255, 255, 0.55) 0 1px, transparent 1.25px),
                    radial-gradient(circle at 88% 82%, rgba(255, 255, 255, 0.7) 0 1.15px, transparent 1.35px),
                    radial-gradient(circle at 8% 58%, rgba(255, 255, 255, 0.48) 0 1px, transparent 1.25px);
                background-size: 340px 340px, 420px 420px, 520px 520px, 410px 410px, 470px 470px, 560px 560px;
                animation: twinkle 14s linear infinite;
                opacity: 0.7;
            }

            body::after {
                background:
                    radial-gradient(circle at 15% 20%, rgba(123, 216, 255, 0.16), transparent 18%),
                    radial-gradient(circle at 85% 18%, rgba(255, 123, 199, 0.18), transparent 22%),
                    radial-gradient(circle at 70% 72%, rgba(125, 127, 255, 0.22), transparent 24%),
                    radial-gradient(circle at 18% 82%, rgba(123, 216, 255, 0.12), transparent 20%);
                filter: blur(42px);
                animation: drift 22s ease-in-out infinite alternate;
                opacity: 0.95;
            }

            a {
                color: inherit;
                text-decoration: none;
            }

            p,
            li,
            td,
            th,
            span,
            label,
            input,
            select,
            textarea {
                letter-spacing: 0.01em;
            }

            strong {
                color: var(--text);
            }

            .container {
                width: min(1180px, calc(100% - 32px));
                margin: 0 auto;
            }

            .site-header {
                position: sticky;
                top: 0;
                z-index: 200;
                padding: 18px 0 0;
            }

            .header-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 18px;
                padding: 16px 18px;
                border: 1px solid var(--line);
                border-radius: 24px;
                background: rgba(7, 12, 31, 0.64);
                backdrop-filter: blur(20px);
                box-shadow: var(--shadow-soft);
            }

            .brand {
                display: inline-flex;
                align-items: center;
                gap: 14px;
                min-width: 0;
            }

            .brand::before {
                content: "";
                width: 14px;
                height: 14px;
                border-radius: 50%;
                background: linear-gradient(135deg, var(--primary), var(--accent));
                box-shadow:
                    0 0 0 6px rgba(123, 216, 255, 0.08),
                    0 0 24px rgba(123, 216, 255, 0.55);
                flex: 0 0 auto;
            }

            .brand-text {
                display: grid;
                gap: 2px;
            }

            .brand-title {
                font-family: "Space Grotesk", "Plus Jakarta Sans", sans-serif;
                font-size: 1.3rem;
                font-weight: 700;
                color: var(--text);
                letter-spacing: 0.03em;
            }

            .brand-subtitle {
                color: var(--muted);
                font-size: 0.8rem;
                text-transform: uppercase;
                letter-spacing: 0.18em;
            }

            .header-meta {
                display: flex;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
            }

            .user-chip,
            .role-badge {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                min-height: 38px;
                padding: 0 14px;
                border-radius: var(--radius-pill);
                border: 1px solid var(--line);
                background: rgba(12, 20, 48, 0.72);
                color: var(--muted-strong);
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
                font-size: 0.92rem;
                font-weight: 700;
            }

            .role-badge {
                text-transform: capitalize;
                color: var(--primary);
            }

            .button,
            button {
                position: relative;
                isolation: isolate;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 44px;
                padding: 0 18px;
                border-radius: 14px;
                border: 1px solid transparent;
                cursor: pointer;
                font: inherit;
                font-weight: 700;
                letter-spacing: 0.01em;
                overflow: hidden;
                transition:
                    transform 180ms ease,
                    border-color 180ms ease,
                    box-shadow 180ms ease,
                    background 180ms ease,
                    color 180ms ease;
            }

            .button::before,
            button::before {
                content: "";
                position: absolute;
                inset: 0;
                background: linear-gradient(120deg, transparent 20%, rgba(255, 255, 255, 0.18) 50%, transparent 80%);
                transform: translateX(-140%);
                transition: transform 0.8s ease;
                z-index: -1;
            }

            .button:hover::before,
            button:hover::before {
                transform: translateX(140%);
            }

            .button:hover,
            button:hover {
                transform: translateY(-2px);
                box-shadow: 0 16px 34px rgba(0, 0, 0, 0.28);
            }

            .button:active,
            button:active {
                transform: translateY(0);
            }

            .button-primary {
                background: linear-gradient(135deg, var(--primary-strong), var(--primary));
                color: #06111d;
                box-shadow: 0 12px 28px rgba(123, 216, 255, 0.2);
            }

            .button-secondary {
                background: linear-gradient(135deg, rgba(20, 35, 82, 0.92), rgba(14, 24, 59, 0.92));
                color: var(--text);
                border-color: rgba(123, 216, 255, 0.28);
            }

            .button-plain {
                background: rgba(12, 19, 46, 0.78);
                color: var(--muted-strong);
                border-color: var(--line);
            }

            .page {
                position: relative;
                padding: 24px 0 56px;
            }

            .hero-card,
            .panel,
            .mini-card,
            .auth-card,
            .demo-box {
                position: relative;
                background: linear-gradient(180deg, rgba(16, 24, 60, 0.86), rgba(7, 13, 35, 0.82));
                border: 1px solid var(--line);
                border-radius: var(--radius-xl);
                box-shadow: var(--shadow-soft);
                backdrop-filter: blur(18px);
            }

            .hero-card::after,
            .panel::after,
            .mini-card::after,
            .auth-card::after,
            .demo-box::after {
                content: "";
                position: absolute;
                inset: 0;
                border-radius: inherit;
                padding: 1px;
                background: linear-gradient(135deg, rgba(123, 216, 255, 0.34), transparent 35%, transparent 65%, rgba(255, 123, 199, 0.22));
                -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
                -webkit-mask-composite: xor;
                mask-composite: exclude;
                pointer-events: none;
                opacity: 0.85;
            }

            .hero-card,
            .panel,
            .mini-card,
            .auth-hero,
            .auth-card {
                animation: rise-in 620ms ease both;
            }

            .hero-card {
                padding: 34px;
                margin-bottom: 20px;
                overflow: hidden;
            }

            .hero-card::before {
                content: "";
                position: absolute;
                width: 240px;
                height: 240px;
                right: -80px;
                top: -70px;
                background: radial-gradient(circle, rgba(123, 216, 255, 0.22), transparent 70%);
                filter: blur(12px);
                pointer-events: none;
            }

            .hero-card h1,
            .auth-card h2,
            .panel h2,
            .mini-card h2,
            .auth-hero h1 {
                margin-top: 0;
                font-family: "Space Grotesk", "Plus Jakarta Sans", sans-serif;
                letter-spacing: 0.01em;
            }

            .hero-card h1 {
                margin-bottom: 12px;
                color: var(--text);
                font-size: clamp(2.2rem, 3vw, 3.2rem);
                line-height: 1.05;
            }

            .hero-card p {
                margin: 0;
                max-width: 760px;
                color: var(--muted);
                font-size: 1.03rem;
                line-height: 1.8;
            }

            .page-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 18px;
                align-items: start;
            }

            .panel-grid {
                display: grid;
                grid-template-columns: minmax(0, 1.45fr) minmax(300px, 0.92fr);
                gap: 18px;
                align-items: start;
            }

            .mini-card {
                padding: 24px;
                height: 100%;
            }

            .mini-card h2,
            .panel h2 {
                margin-bottom: 12px;
                color: var(--text);
                font-size: 1.24rem;
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
                line-height: 1.7;
                font-size: 0.98rem;
            }

            .mini-card ul,
            .panel ul {
                margin: 0;
                padding-left: 20px;
            }

            .mini-card ul li + li,
            .panel ul li + li {
                margin-top: 10px;
            }

            .panel {
                padding: 24px;
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
                font-size: 0.92rem;
                font-weight: 700;
            }

            .section-stack {
                display: grid;
                gap: 18px;
            }

            .stat-row {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 16px;
                margin-top: 24px;
            }

            .stat-box {
                position: relative;
                padding: 20px;
                border-radius: var(--radius-lg);
                background: linear-gradient(180deg, rgba(17, 27, 67, 0.88), rgba(10, 18, 46, 0.88));
                border: 1px solid rgba(123, 216, 255, 0.16);
                overflow: hidden;
            }

            .stat-box::before {
                content: "";
                position: absolute;
                inset: auto auto -40px -20px;
                width: 120px;
                height: 120px;
                background: radial-gradient(circle, rgba(255, 123, 199, 0.18), transparent 70%);
                filter: blur(12px);
                pointer-events: none;
            }

            .stat-box strong {
                display: block;
                margin-bottom: 8px;
                color: var(--text);
                font-family: "Space Grotesk", "Plus Jakarta Sans", sans-serif;
                font-size: clamp(1.8rem, 2vw, 2.3rem);
            }

            .stat-kicker {
                display: inline-block;
                margin-bottom: 10px;
                color: var(--primary);
                font-size: 0.78rem;
                font-weight: 800;
                letter-spacing: 0.12em;
                text-transform: uppercase;
            }

            .stat-box span {
                display: block;
                color: var(--muted);
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
                padding: 36px 16px;
            }

            .auth-grid {
                width: min(1120px, 100%);
                display: grid;
                grid-template-columns: 1.08fr 0.92fr;
                gap: 22px;
            }

            .auth-hero {
                position: relative;
                padding: 46px 38px;
                border-radius: 32px;
                overflow: hidden;
                background:
                    radial-gradient(circle at 25% 25%, rgba(123, 216, 255, 0.28), transparent 30%),
                    radial-gradient(circle at 78% 20%, rgba(255, 123, 199, 0.26), transparent 28%),
                    linear-gradient(145deg, rgba(18, 28, 74, 0.92), rgba(7, 12, 34, 0.96));
                border: 1px solid rgba(123, 216, 255, 0.18);
                box-shadow: var(--shadow-strong);
            }

            .auth-hero::before {
                content: "";
                position: absolute;
                inset: -2px;
                background:
                    linear-gradient(135deg, rgba(123, 216, 255, 0.24), transparent 30%, transparent 70%, rgba(255, 123, 199, 0.2));
                mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
                -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
                padding: 1px;
                -webkit-mask-composite: xor;
                mask-composite: exclude;
                border-radius: inherit;
                pointer-events: none;
            }

            .eyebrow {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                margin: 0 0 16px;
                font-size: 0.82rem;
                font-weight: 800;
                letter-spacing: 0.22em;
                text-transform: uppercase;
                color: var(--primary);
            }

            .eyebrow::before {
                content: "";
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background: linear-gradient(135deg, var(--primary), var(--accent));
                box-shadow: 0 0 18px rgba(123, 216, 255, 0.6);
            }

            .auth-hero h1 {
                margin-bottom: 16px;
                font-size: clamp(2.4rem, 4vw, 4rem);
                line-height: 1.02;
                color: var(--text);
            }

            .auth-hero p,
            .auth-hero li {
                margin: 0;
                color: rgba(235, 241, 255, 0.9);
                line-height: 1.8;
                font-size: 1rem;
            }

            .auth-hero ul {
                margin: 22px 0 0;
                padding-left: 20px;
            }

            .auth-card {
                padding: 32px 28px;
                border-radius: 30px;
            }

            .auth-card h2 {
                margin-bottom: 8px;
                color: var(--text);
                font-size: 2.2rem;
            }

            .auth-card > p {
                margin: 0 0 22px;
                color: var(--muted);
                line-height: 1.75;
            }

            .form-group + .form-group {
                margin-top: 16px;
            }

            label {
                display: block;
                margin-bottom: 8px;
                font-weight: 700;
                color: var(--muted-strong);
            }

            input[type="email"],
            input[type="password"],
            input[type="text"],
            input[type="file"],
            select,
            textarea {
                width: 100%;
                min-height: 50px;
                padding: 12px 14px;
                border: 1px solid var(--line);
                border-radius: 14px;
                background: var(--bg-field);
                color: var(--text);
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.03);
                transition: border-color 180ms ease, box-shadow 180ms ease, transform 180ms ease;
            }

            input::placeholder,
            textarea::placeholder {
                color: rgba(184, 196, 235, 0.7);
            }

            input[type="file"] {
                padding-top: 10px;
            }

            textarea {
                min-height: 144px;
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
                outline: none;
                border-color: var(--line-strong);
                box-shadow: 0 0 0 4px rgba(123, 216, 255, 0.12);
                transform: translateY(-1px);
            }

            input[type="checkbox"] {
                accent-color: var(--primary);
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
                font-weight: 500;
                color: var(--muted);
            }

            .error-box,
            .success-box {
                margin-bottom: 16px;
                padding: 14px 16px;
                border-radius: 16px;
                border: 1px solid transparent;
                backdrop-filter: blur(12px);
            }

            .error-box {
                background: rgba(85, 16, 34, 0.68);
                border-color: rgba(255, 157, 176, 0.24);
                color: #ffd2dc;
            }

            .success-box {
                background: rgba(15, 61, 55, 0.7);
                border-color: rgba(127, 248, 196, 0.26);
                color: #d6ffee;
            }

            .field-help {
                display: block;
                margin-top: 7px;
                color: var(--muted);
                font-size: 0.9rem;
            }

            .form-actions {
                display: flex;
                align-items: center;
                justify-content: flex-start;
                gap: 12px;
                margin-top: 22px;
                flex-wrap: wrap;
            }

            .demo-box {
                margin-top: 20px;
                padding: 18px;
                border-radius: 20px;
                background: linear-gradient(180deg, rgba(13, 21, 52, 0.78), rgba(9, 15, 37, 0.78));
            }

            .demo-box h3 {
                margin: 0 0 12px;
                color: var(--text);
                font-family: "Space Grotesk", "Plus Jakarta Sans", sans-serif;
                font-size: 1rem;
            }

            .demo-box p {
                margin: 7px 0 0;
                color: var(--muted);
                font-size: 0.95rem;
                line-height: 1.7;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            .table-wrap {
                overflow-x: auto;
                border-radius: 18px;
                border: 1px solid rgba(123, 216, 255, 0.12);
                background: rgba(7, 13, 35, 0.35);
            }

            th,
            td {
                padding: 14px 16px;
                text-align: left;
                border-bottom: 1px solid rgba(147, 181, 255, 0.1);
            }

            th {
                background: linear-gradient(135deg, rgba(21, 33, 82, 0.92), rgba(13, 22, 54, 0.92));
                color: var(--text);
                font-size: 0.88rem;
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }

            td {
                color: var(--muted);
                background: rgba(9, 15, 37, 0.2);
            }

            tbody tr:hover td {
                background: rgba(21, 33, 82, 0.28);
            }

            tbody tr:last-child td {
                border-bottom: 0;
            }

            .empty-state {
                padding: 20px;
                border-radius: 18px;
                background: rgba(15, 22, 52, 0.7);
                border: 1px dashed rgba(123, 216, 255, 0.24);
                color: var(--muted);
                line-height: 1.75;
            }

            .status-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 32px;
                padding: 0 12px;
                border-radius: var(--radius-pill);
                font-size: 0.84rem;
                font-weight: 800;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                border: 1px solid transparent;
                white-space: nowrap;
            }

            .compact-list {
                list-style: none;
                margin: 0;
                padding: 0;
                display: grid;
                gap: 12px;
            }

            .compact-list li {
                margin: 0;
                padding-bottom: 12px;
                border-bottom: 1px solid rgba(147, 181, 255, 0.12);
            }

            .compact-list li:last-child {
                padding-bottom: 0;
                border-bottom: 0;
            }

            .list-title {
                display: block;
                color: var(--text);
                font-weight: 700;
                line-height: 1.45;
            }

            .list-meta {
                display: block;
                margin-top: 4px;
                color: var(--muted);
                font-size: 0.92rem;
                line-height: 1.55;
            }

            .status-guide {
                display: grid;
                gap: 12px;
            }

            .status-guide-row {
                display: flex;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
            }

            .status-guide-row span:last-child {
                color: var(--muted);
                font-size: 0.94rem;
            }

            .status-pending {
                background: rgba(255, 216, 133, 0.14);
                color: var(--warning);
                border-color: rgba(255, 216, 133, 0.24);
            }

            .status-in-progress {
                background: rgba(123, 216, 255, 0.14);
                color: var(--primary);
                border-color: rgba(123, 216, 255, 0.24);
            }

            .status-completed {
                background: rgba(127, 248, 196, 0.14);
                color: var(--success);
                border-color: rgba(127, 248, 196, 0.24);
            }

            .text-link {
                color: var(--primary);
                font-weight: 700;
                transition: color 160ms ease, text-shadow 160ms ease;
            }

            .text-link:hover {
                color: #c6efff;
                text-shadow: 0 0 18px rgba(123, 216, 255, 0.3);
            }

            .section-note,
            .muted-text {
                color: var(--muted);
                line-height: 1.75;
            }

            .pagination-bar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
                flex-wrap: wrap;
                margin-top: 18px;
            }

            .pagination-list {
                display: flex;
                align-items: center;
                gap: 8px;
                flex-wrap: wrap;
            }

            .pagination-link,
            .pagination-current {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 42px;
                min-height: 42px;
                padding: 0 14px;
                border-radius: 14px;
                border: 1px solid var(--line);
                background: rgba(11, 18, 42, 0.78);
                color: var(--muted-strong);
                font-weight: 700;
            }

            .pagination-link:hover {
                border-color: var(--line-strong);
                color: var(--text);
            }

            .pagination-current {
                background: linear-gradient(135deg, var(--primary-strong), var(--primary));
                color: #06111d;
                border-color: transparent;
            }

            .pagination-disabled {
                opacity: 0.45;
                pointer-events: none;
            }

            @keyframes rise-in {
                from {
                    opacity: 0;
                    transform: translateY(16px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes twinkle {
                0%, 100% {
                    opacity: 0.55;
                    transform: translate3d(0, 0, 0) scale(1);
                }

                50% {
                    opacity: 0.9;
                    transform: translate3d(-10px, 6px, 0) scale(1.02);
                }
            }

            @keyframes drift {
                from {
                    transform: translate3d(0, 0, 0) scale(1);
                }

                to {
                    transform: translate3d(16px, -20px, 0) scale(1.03);
                }
            }

            @media (max-width: 980px) {
                .auth-grid,
                .panel-grid,
                .page-grid,
                .stat-row {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-width: 720px) {
                .container {
                    width: min(100%, calc(100% - 24px));
                }

                .site-header {
                    padding-top: 12px;
                }

                .header-row {
                    align-items: flex-start;
                    padding: 14px;
                }

                .header-meta {
                    width: 100%;
                }

                .hero-card,
                .panel,
                .mini-card,
                .auth-hero,
                .auth-card {
                    padding: 24px 20px;
                }

                .form-actions,
                .header-meta {
                    flex-direction: column;
                    align-items: stretch;
                }

                .button,
                button {
                    width: 100%;
                }

                .brand-title {
                    font-size: 1.1rem;
                }

                .brand-subtitle {
                    font-size: 0.74rem;
                }
            }
        </style>
    </head>
    <body>
        @auth
            <header class="site-header">
                <div class="container">
                    <div class="header-row">
                        <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="brand">
                            <span class="brand-text">
                                <span class="brand-title">CampusConnect</span>
                                <span class="brand-subtitle">Galaxy Service Hub</span>
                            </span>
                        </a>

                        <div class="header-meta">
                            <span class="role-badge">
                                <i class="fas fa-sparkles"></i>
                                {{ auth()->user()->role }}
                            </span>
                            <span class="user-chip">
                                <i class="fas fa-user-astronaut"></i>
                                {{ auth()->user()->name }}
                            </span>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="button button-plain">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>
        @endauth

        @yield('content')
    </body>
</html>
