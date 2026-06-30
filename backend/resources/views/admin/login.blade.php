@php
    $loginLogo = \App\Models\Setting::get('logo_path');
    $siteName = \App\Models\Setting::get('site_name', 'Commerce');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin Login - {{ $siteName }}</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js'])
        @else
            <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
        @endif
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    </head>
    <body class="auth-page">
        <section class="auth-card">
            <aside class="auth-hero">
                <div class="auth-hero-top">
                    <div class="brand">
                        <span class="brand-mark">
                            @if ($loginLogo)
                                <img src="{{ asset('storage/' . $loginLogo) }}" alt="{{ $siteName }} logo">
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20" aria-hidden="true">
                                    <path d="M6 7h12l-1.2 11.2a2 2 0 0 1-2 1.8H9.2a2 2 0 0 1-2-1.8L6 7z"/>
                                    <path d="M9 7V5a3 3 0 0 1 6 0v2"/>
                                </svg>
                            @endif
                        </span>
                        <span>{{ $siteName }}</span>
                    </div>
                    <span class="auth-secure-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="15" height="15" aria-hidden="true">
                            <rect x="4" y="10" width="16" height="10" rx="2"/>
                            <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                        </svg>
                        Secure area
                    </span>
                </div>

                <div class="auth-hero-copy">
                    <p class="eyebrow">Store control</p>
                    <h2>Admin Dashboard</h2>
                    <p>Keep the day moving with a clear view of orders, catalog updates, and customer activity.</p>
                </div>

                <div class="auth-dashboard-preview" aria-hidden="true">
                    <div class="auth-window-bar">
                        <span></span>
                        <span></span>
                        <span></span>
                        <strong>Today</strong>
                    </div>
                    <div class="auth-metric-grid">
                        <div class="auth-metric">
                            <span>Orders</span>
                            <strong>128</strong>
                            <small>+18%</small>
                        </div>
                        <div class="auth-metric">
                            <span>Revenue</span>
                            <strong>$9.4k</strong>
                            <small>Live</small>
                        </div>
                    </div>
                    <div class="auth-progress-list">
                        <div class="auth-progress-row">
                            <span>Paid orders</span>
                            <b>76%</b>
                        </div>
                        <div class="auth-progress"><span class="progress-paid"></span></div>
                        <div class="auth-progress-row">
                            <span>Stock health</span>
                            <b>91%</b>
                        </div>
                        <div class="auth-progress"><span class="progress-stock"></span></div>
                    </div>
                </div>
            </aside>

            <section class="auth-panel">
                <div class="auth-panel-header">
                    <span class="auth-status-pill">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="15" height="15" aria-hidden="true">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <path d="m9 12 2 2 4-5"/>
                        </svg>
                        Protected sign in
                    </span>
                    <div>
                        <p class="eyebrow">Welcome back</p>
                        <h1>Sign in to admin</h1>
                        <p class="lead">{{ $siteName }} dashboard access</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.login.store') }}" class="auth-form">
                    @csrf

                    <label for="email">
                        Email
                        <span class="input-affix">
                            <span class="affix">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                            </span>
                            <input id="email" type="email" name="email" value="{{ old('email', 'admin@gmail.com') }}" placeholder="you@example.com" autocomplete="username" required autofocus>
                        </span>
                    </label>

                    <label for="password">
                        Password
                        <span class="input-affix">
                            <span class="affix">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                            </span>
                            <input id="password" type="password" name="password" placeholder="Your password" autocomplete="current-password" required>
                        </span>
                    </label>

                    <div class="auth-helper">
                        <label class="check-row" for="remember">
                            <input id="remember" type="checkbox" name="remember" value="1">
                            Remember me
                        </label>
                        <a href="{{ route('admin.password.request') }}">Forgot password?</a>
                    </div>

                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror

                    <button class="btn btn-primary full auth-submit" type="submit">
                        <span>Continue to dashboard</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
                    </button>

                    <div class="demo-card">
                        <span class="demo-card-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true">
                                <path d="M8 7h8"/>
                                <path d="M8 12h8"/>
                                <path d="M8 17h5"/>
                                <rect x="4" y="3" width="16" height="18" rx="2"/>
                            </svg>
                        </span>
                        <span class="demo-card-copy">
                            <strong>Demo credentials</strong>
                            <span>Email <code>admin@gmail.com</code></span>
                            <span>Password <code>1234567</code></span>
                        </span>
                    </div>
                </form>
            </section>
        </section>
    </body>
</html>
