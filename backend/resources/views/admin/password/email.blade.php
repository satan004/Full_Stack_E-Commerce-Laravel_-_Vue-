<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Forgot Password · Commerce Admin</title>
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
        <section class="auth-card" style="grid-template-columns: 1fr; max-width: 480px;">
            <section class="auth-panel">
                <div>
                    <p class="eyebrow">Account recovery</p>
                    <h1>Forgot your password?</h1>
                    <p class="lead">Enter your admin email and we'll send you a link to reset your password.</p>
                </div>

                @if (session('status'))
                    <div class="notice">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('admin.password.email') }}" class="auth-form">
                    @csrf

                    <label>
                        Email
                        <span class="input-affix">
                            <span class="affix">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                            </span>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                        </span>
                    </label>

                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror

                    <button class="btn btn-primary full" type="submit" style="min-height: 2.7rem;">
                        Send Reset Link
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
                    </button>

                    <div class="demo-card">
                        <strong>Need help?</strong>
                        <span>Remember your password? <a href="{{ route('admin.login') }}" style="color: var(--primary); font-weight: 600;">Back to login</a></span>
                    </div>
                </form>
            </section>
        </section>
    </body>
</html>
