@php
    use App\Models\Setting;

    $siteName = Setting::get('site_name', 'Commerce');
    $faviconPath = Setting::get('favicon_path');
    $logoPath = Setting::get('logo_path');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Admin') · {{ $siteName }} Admin</title>
        @if ($faviconPath)
            <link rel="icon" type="image/png" href="{{ asset('storage/' . $faviconPath) }}">
        @endif
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js'])
        @else
            <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
        @endif
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    </head>
    <body class="admin-body">
        <div class="admin-shell">
            <aside class="admin-sidebar">
                <a class="admin-brand" href="{{ route('admin.dashboard') }}">
                    <span class="admin-brand-mark">
                        @if ($logoPath)
                            <img src="{{ asset('storage/' . $logoPath) }}" alt="{{ $siteName }} logo">
                        @else
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" width="20" height="20">
                                <path d="M12 2 4 7v6c0 5 3.5 8.5 8 9 4.5-.5 8-4 8-9V7l-8-5z" opacity=".25"/>
                                <path d="M12 6.5 7.5 9v3.5c0 3 2 5 4.5 5.5 2.5-.5 4.5-2.5 4.5-5.5V9L12 6.5z"/>
                                <path d="M12 9.5 9.5 11v1.7c0 1.5 1 2.6 2.5 2.9 1.5-.3 2.5-1.4 2.5-2.9V11L12 9.5z" fill="#fff"/>
                            </svg>
                        @endif
                    </span>
                    <span>{{ $siteName }}</span>
                </a>

                <div class="admin-profile-card">
                    @if (auth()->user()?->avatar_url)
                        <span class="avatar"><img src="{{ auth()->user()->avatar_url }}" alt=""></span>
                    @else
                        <span class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
                    @endif
                    <div class="who">
                        <strong>{{ auth()->user()->name ?? 'Admin' }}</strong>
                        <span>{{ auth()->user()->is_admin ?? false ? 'Administrator' : 'User' }}</span>
                    </div>
                </div>

                @php
                    $icon = fn (string $name) => match ($name) {
                        'dashboard' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>',
                        'category' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><rect x="3" y="3" width="7" height="7" rx="1.2"/><rect x="14" y="3" width="7" height="7" rx="1.2"/><rect x="3" y="14" width="7" height="7" rx="1.2"/><rect x="14" y="14" width="7" height="7" rx="1.2"/></svg>',
                        'product' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M3 7l9-4 9 4-9 4-9-4z"/><path d="M3 12l9 4 9-4"/><path d="M3 17l9 4 9-4"/></svg>',
                        'order' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M5 4h14l-1.5 12.5a2 2 0 0 1-2 1.7H8.5a2 2 0 0 1-2-1.7L5 4z"/><path d="M9 8v0M15 8v0"/></svg>',
                        'user' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>',
                        'settings' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3h0a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5h0a1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8v0a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/></svg>',
                        default => '',
                    };
                @endphp

                <nav class="admin-nav">
                    <a href="{{ route('admin.dashboard') }}" @class(['active' => request()->routeIs('admin.dashboard')])>
                        <span class="admin-nav-icon">{!! $icon('dashboard') !!}</span> Dashboard
                    </a>
                    <a href="{{ route('admin.categories.index') }}" @class(['active' => request()->routeIs('admin.categories.*')])>
                        <span class="admin-nav-icon">{!! $icon('category') !!}</span> Categories
                    </a>
                    <a href="{{ route('admin.products.index') }}" @class(['active' => request()->routeIs('admin.products.*')])>
                        <span class="admin-nav-icon">{!! $icon('product') !!}</span> Products
                    </a>
                    <a href="{{ route('admin.orders.index') }}" @class(['active' => request()->routeIs('admin.orders.*')])>
                        <span class="admin-nav-icon">{!! $icon('order') !!}</span> Orders
                    </a>
                    <a href="{{ route('admin.users.index') }}" @class(['active' => request()->routeIs('admin.users.*')])>
                        <span class="admin-nav-icon">{!! $icon('user') !!}</span> Users
                    </a>
                </nav>

                <p class="admin-nav-section">Account</p>
                <nav class="admin-nav">
                    <a href="{{ route('admin.profile.edit') }}" @class(['active' => request()->routeIs('admin.profile.*')])>
                        <span class="admin-nav-icon">{!! $icon('user') !!}</span> My Profile
                    </a>
                    <a href="{{ route('admin.settings.index') }}" @class(['active' => request()->routeIs('admin.settings.*')])>
                        <span class="admin-nav-icon">{!! $icon('settings') !!}</span> General Settings
                    </a>
                </nav>

                <div class="admin-sidebar-foot">
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="btn full" type="submit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M15 12H3"/><path d="M7 8l-4 4 4 4"/><path d="M11 4h7a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-7"/></svg>
                            Logout
                        </button>
                    </form>
                </div>
            </aside>

            <main class="admin-main">
                <header class="admin-topbar">
                    <div class="admin-topbar-search">
                        <span class="affix">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                        </span>
                        <input type="search" placeholder="Search products, orders, users...">
                    </div>

                    <div class="admin-topbar-actions">
                        <button class="icon-btn" type="button" aria-label="Apps">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                        </button>
                        <button class="icon-btn" type="button" aria-label="Messages">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M4 6h16v10a2 2 0 0 1-2 2H8l-4 4z"/></svg>
                            <span class="dot"></span>
                        </button>
                        <button class="icon-btn" type="button" aria-label="Notifications">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M6 8a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6z"/><path d="M10 19a2 2 0 0 0 4 0"/></svg>
                            <span class="dot"></span>
                        </button>
                        <a class="admin-userchip" href="{{ route('admin.profile.edit') }}">
                            @if (auth()->user()?->avatar_url)
                                <span class="avatar"><img src="{{ auth()->user()->avatar_url }}" alt=""></span>
                            @else
                                <span class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
                            @endif
                            <span class="name">{{ auth()->user()->name ?? 'Admin' }}</span>
                            <svg class="caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><path d="m6 9 6 6 6-6"/></svg>
                        </a>
                    </div>
                </header>

                <div class="admin-content">
                    @if (session('status'))
                        <div class="notice">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any() && ! request()->routeIs('admin.login'))
                        <div class="notice danger">
                            <strong>Please check the form.</strong>
                            <ul style="margin: 0.25rem 0 0 1rem;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </body>
</html>
