<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Reset Password · Commerce Admin</title>
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
                    <p class="eyebrow">Set a new password</p>
                    <h1>Reset Password</h1>
                    <p class="lead">Choose a strong new password for your admin account.</p>
                </div>

                @if ($errors->any())
                    <div class="notice danger">
                        <strong>{{ $errors->first() }}</strong>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.password.store') }}" class="auth-form">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <label>
                        Email
                        <input type="email" name="email" value="{{ old('email', $email) }}" required readonly style="background: var(--surface-2); color: var(--muted);">
                    </label>

                    <label>
                        New Password
                        <span class="input-affix">
                            <span class="affix">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                            </span>
                            <input type="password" name="password" placeholder="At least 6 characters" required autofocus>
                        </span>
                    </label>

                    <label>
                        Confirm New Password
                        <input type="password" name="password_confirmation" placeholder="Repeat the new password" required>
                    </label>

                    <button class="btn btn-primary full" type="submit" style="min-height: 2.7rem;">
                        Reset Password
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M5 12l4 4L19 6"/></svg>
                    </button>

                    <div class="demo-card">
                        <strong>Security tip</strong>
                        <span>Use a mix of letters, numbers and symbols. Never reuse passwords from other sites.</span>
                    </div>
                </form>
            </section>
        </section>
    </body>
</html>
