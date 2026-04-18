<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <title>@yield('title', 'CampusConnect')</title>

        <style>
            :root {
                --bg-base: #fbf9fe;
                --bg-deep: #f1ebfa;
                --bg-surface: rgba(255, 255, 255, 0.96);
                --bg-surface-strong: #ffffff;
                --bg-surface-soft: #f6f1fc;
                --bg-field: #fcfaff;
                --line: rgba(95, 67, 167, 0.14);
                --line-strong: rgba(95, 67, 167, 0.28);
                --text: #2f2347;
                --muted: #726785;
                --muted-strong: #54486d;
                --primary: #5f43a7;
                --primary-strong: #7158bb;
                --accent: #8eb5e8;
                --success: #2f9465;
                --warning: #b97b20;
                --danger: #bb5875;
                --shadow-soft: 0 16px 42px rgba(63, 40, 111, 0.08);
                --shadow-strong: 0 28px 76px rgba(63, 40, 111, 0.14);
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
                font-family: "Montserrat", "Segoe UI", sans-serif;
                color: var(--text);
                background:
                    radial-gradient(circle at top left, rgba(95, 67, 167, 0.1), transparent 32%),
                    radial-gradient(circle at 88% 16%, rgba(142, 181, 232, 0.18), transparent 22%),
                    linear-gradient(180deg, #ffffff 0%, #faf7fe 52%, #f3edf9 100%);
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
                background:
                    linear-gradient(180deg, rgba(95, 67, 167, 0.06), transparent 180px),
                    linear-gradient(90deg, rgba(95, 67, 167, 0.05) 1px, transparent 1px),
                    linear-gradient(180deg, rgba(95, 67, 167, 0.04) 1px, transparent 1px);
                background-size: auto, 48px 48px, 48px 48px;
                opacity: 0.35;
            }

            body::after {
                background:
                    radial-gradient(circle at 16% 18%, rgba(95, 67, 167, 0.13), transparent 22%),
                    radial-gradient(circle at 82% 12%, rgba(142, 181, 232, 0.16), transparent 24%),
                    radial-gradient(circle at 75% 82%, rgba(113, 88, 187, 0.12), transparent 22%);
                filter: blur(60px);
                animation: drift 28s ease-in-out infinite alternate;
                opacity: 0.9;
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
                background: rgba(255, 255, 255, 0.92);
                backdrop-filter: blur(14px);
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
                    0 0 0 6px rgba(95, 67, 167, 0.08),
                    0 12px 22px rgba(95, 67, 167, 0.16);
                flex: 0 0 auto;
            }

            .brand-text {
                display: grid;
                gap: 2px;
            }

            .brand-title {
                font-family: "Montserrat", "Segoe UI", sans-serif;
                font-size: 1.3rem;
                font-weight: 800;
                color: var(--text);
                letter-spacing: 0.03em;
            }

            .brand-subtitle {
                color: var(--muted);
                font-size: 0.8rem;
                text-transform: uppercase;
                letter-spacing: 0.14em;
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
                background: rgba(255, 255, 255, 0.9);
                color: var(--muted-strong);
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
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
                background: linear-gradient(120deg, transparent 20%, rgba(255, 255, 255, 0.22) 50%, transparent 80%);
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
                box-shadow: 0 14px 32px rgba(63, 40, 111, 0.12);
            }

            .button:active,
            button:active {
                transform: translateY(0);
            }

            .button-primary {
                background: linear-gradient(135deg, var(--primary-strong), var(--primary));
                color: #ffffff;
                box-shadow: 0 12px 28px rgba(95, 67, 167, 0.22);
            }

            .button-secondary {
                background: linear-gradient(135deg, #ffffff, #f6f1fc);
                color: var(--primary);
                border-color: rgba(95, 67, 167, 0.24);
            }

            .button-plain {
                background: rgba(245, 239, 252, 0.92);
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
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 244, 253, 0.96));
                border: 1px solid var(--line);
                border-radius: var(--radius-xl);
                box-shadow: var(--shadow-soft);
                backdrop-filter: blur(12px);
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
                background: linear-gradient(135deg, rgba(95, 67, 167, 0.18), transparent 38%, transparent 62%, rgba(142, 181, 232, 0.18));
                -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
                -webkit-mask-composite: xor;
                mask-composite: exclude;
                pointer-events: none;
                opacity: 1;
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
                background: radial-gradient(circle, rgba(95, 67, 167, 0.12), transparent 70%);
                filter: blur(18px);
                pointer-events: none;
            }

            .hero-card h1,
            .auth-card h2,
            .panel h2,
            .mini-card h2,
            .auth-hero h1 {
                margin-top: 0;
                font-family: "Montserrat", "Segoe UI", sans-serif;
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

            .dashboard-duo-grid {
                display: grid;
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
                gap: 18px;
                align-items: stretch;
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

            .mini-card-stack {
                display: grid;
                gap: 18px;
            }

            .mini-card-section + .mini-card-section {
                padding-top: 18px;
                border-top: 1px solid rgba(95, 67, 167, 0.12);
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
                background: linear-gradient(180deg, #fbf8ff, #f2ebfa);
                border: 1px solid rgba(95, 67, 167, 0.14);
                overflow: hidden;
            }

            .stat-box::before {
                content: "";
                position: absolute;
                inset: auto auto -40px -20px;
                width: 120px;
                height: 120px;
                background: radial-gradient(circle, rgba(142, 181, 232, 0.18), transparent 70%);
                filter: blur(18px);
                pointer-events: none;
            }

            .stat-box strong {
                display: block;
                margin-bottom: 8px;
                color: var(--primary);
                font-family: "Montserrat", "Segoe UI", sans-serif;
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

            .stat-box .status-badge {
                display: flex;
                width: 100%;
                justify-content: center;
                text-align: center;
            }

            .stat-box .status-badge.status-pending {
                background: rgba(185, 123, 32, 0.1);
                color: var(--warning);
                border-color: rgba(185, 123, 32, 0.16);
            }

            .stat-box .status-badge.status-in-progress {
                background: rgba(95, 67, 167, 0.1);
                color: var(--primary);
                border-color: rgba(95, 67, 167, 0.16);
            }

            .stat-box .status-badge.status-completed {
                background: rgba(47, 148, 101, 0.1);
                color: var(--success);
                border-color: rgba(47, 148, 101, 0.16);
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
                    radial-gradient(circle at 20% 18%, rgba(255, 255, 255, 0.16), transparent 30%),
                    radial-gradient(circle at 85% 18%, rgba(142, 181, 232, 0.28), transparent 28%),
                    linear-gradient(145deg, #735cc0, #5a3f9f);
                border: 1px solid rgba(95, 67, 167, 0.2);
                box-shadow: var(--shadow-strong);
            }

            .auth-hero::before {
                content: "";
                position: absolute;
                inset: -2px;
                background:
                    linear-gradient(135deg, rgba(255, 255, 255, 0.34), transparent 30%, transparent 70%, rgba(142, 181, 232, 0.18));
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
                box-shadow: 0 0 18px rgba(255, 255, 255, 0.26);
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
                color: rgba(255, 255, 255, 0.92);
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

            .auth-grid-compact {
                width: min(460px, 100%);
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .auth-card-minimal {
                padding: 32px 28px 26px;
                border-radius: 24px;
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(250, 247, 253, 0.98));
            }

            .auth-card-minimal::after {
                opacity: 0.75;
            }

            .auth-card-intro {
                margin-bottom: 22px;
            }

            .auth-card-intro .eyebrow {
                margin-bottom: 14px;
            }

            .auth-card-intro h1 {
                margin: 0 0 10px;
                color: var(--text);
                font-family: "Montserrat", "Segoe UI", sans-serif;
                font-size: clamp(2rem, 4vw, 2.5rem);
                line-height: 1.05;
            }

            .auth-form {
                display: grid;
                gap: 16px;
            }

            .auth-form .form-group + .form-group {
                margin-top: 0;
            }

            .auth-submit {
                width: 100%;
                margin-top: 2px;
            }

            .auth-devnote {
                margin-top: 18px;
                border: 1px solid rgba(95, 67, 167, 0.12);
                border-radius: 16px;
                background: linear-gradient(180deg, #faf7fe, #f5effb);
                overflow: hidden;
            }

            .auth-devnote summary {
                list-style: none;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 14px 16px;
                cursor: pointer;
                color: var(--primary);
                font-size: 0.94rem;
                font-weight: 800;
            }

            .auth-devnote summary::-webkit-details-marker {
                display: none;
            }

            .auth-devnote summary::after {
                content: "+";
                font-size: 1rem;
                font-weight: 800;
            }

            .auth-devnote[open] summary::after {
                content: "-";
            }

            .auth-devnote-body {
                padding: 0 16px 16px;
                border-top: 1px solid rgba(95, 67, 167, 0.1);
            }

            .auth-devnote-body p {
                margin: 12px 0 0;
                color: var(--muted);
                font-size: 0.93rem;
                line-height: 1.7;
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
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85);
                transition: border-color 180ms ease, box-shadow 180ms ease, transform 180ms ease;
            }

            input::placeholder,
            textarea::placeholder {
                color: rgba(114, 103, 133, 0.7);
            }

            input[type="file"] {
                padding-top: 10px;
            }

            input[type="file"]::file-selector-button {
                margin-right: 12px;
                min-height: 36px;
                padding: 8px 14px;
                border: 1px solid rgba(95, 67, 167, 0.24);
                border-radius: 10px;
                background: linear-gradient(135deg, #ffffff, #f6f1fc);
                color: var(--primary);
                font: inherit;
                font-size: 0.9rem;
                font-weight: 700;
                letter-spacing: 0.01em;
                cursor: pointer;
                transition: border-color 160ms ease, box-shadow 160ms ease, transform 160ms ease;
            }

            input[type="file"]::file-selector-button:hover {
                border-color: var(--line-strong);
                box-shadow: 0 10px 20px rgba(63, 40, 111, 0.1);
                transform: translateY(-1px);
            }

            input[type="file"]::file-selector-button:active {
                transform: translateY(0);
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
                box-shadow: 0 0 0 4px rgba(95, 67, 167, 0.12);
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
                margin: 2px 0 8px;
            }

            .checkbox-wrap {
                position: relative;
                display: inline-flex;
                align-items: center;
                gap: 10px;
                margin: 0;
                padding: 0;
                cursor: pointer;
                transition: color 180ms ease;
            }

            .checkbox-wrap:hover {
                color: var(--text);
            }

            .checkbox-wrap input {
                position: absolute;
                opacity: 0;
                pointer-events: none;
            }

            .checkbox-indicator {
                position: relative;
                width: 18px;
                height: 18px;
                flex: 0 0 18px;
                border-radius: 5px;
                border: 1px solid rgba(95, 67, 167, 0.28);
                background: rgba(255, 255, 255, 0.92);
                box-shadow:
                    inset 0 1px 0 rgba(255, 255, 255, 0.96),
                    0 1px 2px rgba(63, 40, 111, 0.06);
                transition: border-color 180ms ease, background 180ms ease, box-shadow 180ms ease, transform 180ms ease;
            }

            .checkbox-indicator::after {
                content: "";
                position: absolute;
                left: 4px;
                top: 4px;
                width: 7px;
                height: 4px;
                border-left: 2px solid #ffffff;
                border-bottom: 2px solid #ffffff;
                opacity: 0;
                transform: rotate(-45deg) scale(0.65);
                transition: opacity 160ms ease, transform 180ms ease;
            }

            .checkbox-text {
                margin: 0;
                color: var(--muted-strong);
                font-size: 0.94rem;
                font-weight: 600;
                letter-spacing: 0.01em;
                transition: color 180ms ease;
            }

            .checkbox-wrap input:focus-visible + .checkbox-indicator {
                box-shadow:
                    inset 0 1px 0 rgba(255, 255, 255, 0.96),
                    0 0 0 4px rgba(95, 67, 167, 0.12),
                    0 1px 2px rgba(63, 40, 111, 0.06);
            }

            .checkbox-wrap input:checked + .checkbox-indicator {
                border-color: transparent;
                background: linear-gradient(135deg, var(--primary-strong), var(--primary));
                box-shadow:
                    0 4px 10px rgba(95, 67, 167, 0.18),
                    inset 0 1px 0 rgba(255, 255, 255, 0.16);
            }

            .checkbox-wrap input:checked + .checkbox-indicator::after {
                opacity: 1;
                transform: rotate(-45deg) scale(1);
            }

            .checkbox-wrap input:checked ~ .checkbox-text {
                color: var(--text);
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
                background: #fff4f7;
                border-color: rgba(187, 88, 117, 0.18);
                color: #9b445e;
            }

            .success-box {
                background: #effaf5;
                border-color: rgba(47, 148, 101, 0.16);
                color: #236e4d;
            }

            .warning-box {
                margin-bottom: 16px;
                padding: 14px 16px;
                border-radius: 16px;
                background: #fff7ed;
                border: 1px solid rgba(185, 123, 32, 0.22);
                color: #9a5a14;
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
                background: linear-gradient(180deg, #faf7fe, #f3edf9);
            }

            .demo-box h3 {
                margin: 0 0 12px;
                color: var(--primary);
                font-family: "Montserrat", "Segoe UI", sans-serif;
                font-size: 1rem;
            }

            .demo-box p {
                margin: 7px 0 0;
                color: var(--muted);
                font-size: 0.95rem;
                line-height: 1.7;
            }

            .hero-row {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 18px;
                flex-wrap: wrap;
            }

            .hero-chip-row {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 10px;
                flex-wrap: wrap;
            }

            .hero-chip {
                display: inline-flex;
                align-items: center;
                min-height: 38px;
                padding: 0 14px;
                border-radius: var(--radius-pill);
                border: 1px solid rgba(95, 67, 167, 0.16);
                background: rgba(255, 255, 255, 0.94);
                color: var(--muted-strong);
                font-size: 0.92rem;
                font-weight: 700;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
            }

            .stat-row.stat-row-four {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .workspace-grid {
                display: grid;
                grid-template-columns: minmax(0, 1.32fr) minmax(300px, 0.92fr);
                gap: 18px;
                align-items: start;
            }

            .panel-stack,
            .aside-stack,
            .team-list,
            .toolbar-actions {
                display: grid;
                gap: 18px;
            }

            .toolbar-grid {
                display: grid;
                grid-template-columns: minmax(240px, 2fr) repeat(2, minmax(170px, 1fr)) auto;
                gap: 14px;
                align-items: end;
                margin-bottom: 18px;
            }

            .toolbar-grid .form-group + .form-group {
                margin-top: 0;
            }

            .toolbar-actions {
                grid-template-columns: repeat(2, minmax(120px, auto));
                align-items: end;
            }

            .toolbar-actions .button {
                width: 100%;
            }

            .table-cell-stack {
                display: grid;
                gap: 4px;
            }

            .request-snippet {
                color: var(--muted);
                font-size: 0.9rem;
                line-height: 1.6;
            }

            .priority-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 32px;
                padding: 0 12px;
                border-radius: var(--radius-pill);
                border: 1px solid transparent;
                font-size: 0.82rem;
                font-weight: 800;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .priority-urgent {
                background: rgba(187, 88, 117, 0.1);
                color: var(--danger);
                border-color: rgba(187, 88, 117, 0.16);
            }

            .priority-standard {
                background: rgba(47, 148, 101, 0.08);
                color: var(--success);
                border-color: rgba(47, 148, 101, 0.14);
            }

            .team-member,
            .detail-card,
            .panel-slab {
                padding: 16px 18px;
                border-radius: 18px;
                border: 1px solid rgba(95, 67, 167, 0.12);
                background: rgba(255, 255, 255, 0.94);
            }

            .team-member strong,
            .detail-card strong,
            .panel-slab strong {
                display: block;
                color: var(--text);
            }

            .team-member span,
            .detail-card span,
            .panel-slab span {
                display: block;
                color: var(--muted);
                line-height: 1.6;
            }

            .detail-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 14px;
                margin-bottom: 14px;
            }

            .panel-slab + .panel-slab {
                margin-top: 14px;
            }

            .pill-list {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }

            .ticket-summary-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 16px;
                margin-top: 24px;
            }

            .ticket-summary-card {
                display: flex;
                flex-direction: column;
                gap: 12px;
                min-height: 186px;
                padding: 20px 20px 18px;
                border-radius: var(--radius-lg);
                border: 1px solid rgba(95, 67, 167, 0.14);
                background: linear-gradient(180deg, #fbf8ff, #f4eefb);
            }

            .ticket-summary-value {
                display: flex;
                align-items: flex-start;
                min-height: 70px;
                color: var(--primary);
                font-family: "Montserrat", "Segoe UI", sans-serif;
                font-size: clamp(1.2rem, 1.8vw, 1.75rem);
                font-weight: 800;
                line-height: 1.18;
                overflow-wrap: anywhere;
            }

            .ticket-summary-value .status-badge,
            .ticket-summary-value .priority-badge {
                margin-top: 2px;
            }

            .ticket-summary-meta {
                margin-top: auto;
                color: var(--muted);
                line-height: 1.6;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            .table-wrap {
                overflow-x: auto;
                border-radius: 18px;
                border: 1px solid rgba(95, 67, 167, 0.12);
                background: rgba(255, 255, 255, 0.92);
            }

            th,
            td {
                padding: 14px 16px;
                text-align: left;
                border-bottom: 1px solid rgba(95, 67, 167, 0.08);
            }

            th {
                background: linear-gradient(135deg, #f7f1fd, #efe7fa);
                color: var(--primary);
                font-size: 0.88rem;
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }

            td {
                color: var(--muted-strong);
                background: rgba(255, 255, 255, 0.92);
            }

            tbody tr:hover td {
                background: #f8f3fd;
            }

            tbody tr:last-child td {
                border-bottom: 0;
            }

            .empty-state {
                padding: 20px;
                border-radius: 18px;
                background: #faf7fe;
                border: 1px dashed rgba(95, 67, 167, 0.2);
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

            .timeline {
                display: grid;
                gap: 14px;
                margin-bottom: 22px;
            }

            .timeline-item {
                padding: 18px;
                border-radius: 18px;
                border: 1px solid var(--line);
                background: #ffffff;
            }

            .timeline-item-student {
                border-color: rgba(95, 67, 167, 0.18);
                background: linear-gradient(180deg, #fbf8ff, #f4eefb);
            }

            .timeline-item-staff {
                border-color: rgba(142, 181, 232, 0.22);
                background: linear-gradient(180deg, #f8fbff, #eef4fd);
            }

            .timeline-item-admin {
                border-color: rgba(113, 88, 187, 0.2);
                background: linear-gradient(180deg, #f9f5fe, #f1eaf9);
            }

            .timeline-item-header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 10px;
                flex-wrap: wrap;
            }

            .timeline-author {
                display: inline-block;
                color: var(--text);
                font-weight: 700;
            }

            .timeline-role,
            .timeline-time {
                color: var(--muted);
                font-size: 0.9rem;
            }

            .timeline-role {
                display: inline-block;
                margin-left: 8px;
            }

            .timeline-message {
                margin: 0;
                color: var(--muted-strong);
                line-height: 1.8;
                white-space: pre-wrap;
            }

            .status-pending {
                background: rgba(185, 123, 32, 0.1);
                color: var(--warning);
                border-color: rgba(185, 123, 32, 0.16);
            }

            .status-in-progress {
                background: rgba(95, 67, 167, 0.1);
                color: var(--primary);
                border-color: rgba(95, 67, 167, 0.16);
            }

            .status-completed {
                background: rgba(47, 148, 101, 0.1);
                color: var(--success);
                border-color: rgba(47, 148, 101, 0.16);
            }

            .text-link {
                color: var(--primary);
                font-weight: 700;
                transition: color 160ms ease;
            }

            .text-link:hover {
                color: #4d358b;
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
                background: rgba(255, 255, 255, 0.96);
                color: var(--muted-strong);
                font-weight: 700;
            }

            .pagination-link:hover {
                border-color: var(--line-strong);
                color: var(--text);
            }

            .pagination-current {
                background: linear-gradient(135deg, var(--primary-strong), var(--primary));
                color: #ffffff;
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
                .dashboard-duo-grid,
                .panel-grid,
                .page-grid,
                .stat-row,
                .workspace-grid,
                .detail-grid,
                .ticket-summary-grid {
                    grid-template-columns: 1fr;
                }

                .toolbar-grid {
                    grid-template-columns: 1fr;
                }

                .toolbar-actions {
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

                .hero-row,
                .hero-chip-row {
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
                                <span class="brand-subtitle">University Service Portal</span>
                            </span>
                        </a>

                        <div class="header-meta">
                            <span class="role-badge">
                                <i class="fas fa-user-shield"></i>
                                {{ auth()->user()->role }}
                            </span>
                            <span class="user-chip">
                                <i class="fas fa-user-graduate"></i>
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
