<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin Login · Commerce</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js'])
        @else
            <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
        @endif
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    </head>
    <body class="auth-page">
        <section class="auth-card">
            <aside class="auth-hero">
                <div class="brand">
                    @php
                        $loginLogo = \App\Models\Setting::get('logo_path');
                        $siteName = \App\Models\Setting::get('site_name', 'Commerce');
                    @endphp
                    <span class="brand-mark">
                        @if ($loginLogo)
                            <img src="{{ asset('storage/' . $loginLogo) }}" alt="{{ $siteName }} logo" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;background:#fff;">
                        @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20" aria-hidden="true">
                                <path d="M6 7h12l-1.2 11.2a2 2 0 0 1-2 1.8H9.2a2 2 0 0 1-2-1.8L6 7z"/>
                                <path d="M9 7V5a3 3 0 0 1 6 0v2"/>
                            </svg>
                        @endif
                    </span>
                    <span>{{ $siteName }} Admin</span>
                </div>

                <div>
                    <p class="eyebrow" style="color: #a5b4fc;">Welcome back</p>
                    <h2>Manage your store from a single, beautiful dashboard.</h2>
                    <p style="margin-top: 0.75rem;">Track orders, update your catalog, manage customers, and keep your store running smoothly — all in one place.</p>
                </div>

                <ul class="auth-feature-list">
                    <li>Real-time order &amp; inventory overview</li>
                    <li>Product and category management</li>
                    <li>Customer accounts &amp; insights</li>
                </ul>
            </aside>

            <section class="auth-panel">
                <div>
                    <p class="eyebrow">Sign in</p>
                    <h1>Admin Login</h1>
                    <p class="lead">Enter your credentials to continue.</p>
                </div>

                <form method="POST" action="{{ route('admin.login.store') }}" class="auth-form">
                    @csrf

                    <label>
                        Email
                        <span class="input-affix">
                            <span class="affix">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                            </span>
                            <input type="email" name="email" value="{{ old('email', 'admin@gmail.com') }}" placeholder="you@example.com" required autofocus>
                        </span>
                    </label>

                    <label>
                        Password
                        <span class="input-affix">
                            <span class="affix">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                            </span>
                            <input type="password" name="password" placeholder="Your password" required>
                        </span>
                    </label>

                    <div class="auth-helper">
                        <label class="check-row">
                            <input type="checkbox" name="remember" value="1">
                            Remember me
                        </label>
                        <a href="{{ route('admin.password.request') }}">Forgot password?</a>
                    </div>

                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror

                    <button class="btn btn-primary full" type="submit" style="min-height: 2.7rem;">
                        Sign in to admin
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
                    </button>

                    <div class="demo-card">
                        <strong>Demo credentials</strong>
                        <span>Email: <code>admin@gmail.com</code> &nbsp;·&nbsp; Password: <code>1234567</code></span>
                    </div>
                </form>
            </section>
        </section>
    </body>
</html>
