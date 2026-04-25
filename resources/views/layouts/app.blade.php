<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'CampusConnect')</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
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

        @unless (request()->routeIs('login'))
            @php
                $assistantUser = auth()->user();
                $assistantRole = $assistantUser?->role;
                $assistantContext = [
                    'assistantEnabled' => filled(config('services.gemini.api_key')),
                    'chatEndpoint' => route('assistant.chat'),
                    'csrfToken' => csrf_token(),
                    'isAuthenticated' => (bool) $assistantUser,
                    'role' => $assistantRole,
                    'currentRoute' => request()->route()?->getName(),
                ];
            @endphp

            <div class="assistant-shell" data-assistant>
                <section id="campusconnect-assistant-panel" class="assistant-panel" data-assistant-panel aria-hidden="true">
                    <div class="assistant-header">
                        <div class="assistant-header-main">
                            <span class="assistant-avatar" aria-hidden="true">
                                <i class="fas fa-comment-dots"></i>
                            </span>
                            <div>
                                <h2>CampusConnect Assistant</h2>
                                <span class="assistant-presence">Online</span>
                            </div>
                        </div>
                        <button type="button" class="assistant-close" data-assistant-close aria-label="Close support assistant">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="assistant-messages" data-assistant-messages aria-live="polite"></div>

                    <div class="assistant-suggestions" data-assistant-suggestions></div>

                    <form class="assistant-form" data-assistant-form>
                        <input
                            type="text"
                            name="assistant_prompt"
                            data-assistant-input
                            placeholder="Message CampusConnect"
                            autocomplete="off"
                        >
                        <button type="submit" class="assistant-submit" aria-label="Send assistant message">
                            <i class="fas fa-arrow-up"></i>
                        </button>
                    </form>
                </section>

                <button type="button" class="assistant-launcher" data-assistant-toggle aria-expanded="false" aria-controls="campusconnect-assistant-panel">
                    <span class="assistant-launcher-icon">
                        <i class="fas fa-comment-dots"></i>
                    </span>
                    <span class="assistant-launcher-copy">
                        <span class="assistant-launcher-label">Need Help?</span>
                        <span class="assistant-launcher-title">Support Assistant</span>
                    </span>
                </button>
            </div>

            <script type="application/json" id="campusconnect-assistant-context">@json($assistantContext)</script>
        @endunless
    </body>
</html>
