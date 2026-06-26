@extends('layouts.admin')

@section('title', 'My Profile')

@section('content')
    <div class="admin-page-header">
        <div>
            <p class="eyebrow">Account</p>
            <h1>My Profile</h1>
            <p>Update your personal information, profile picture and password.</p>
        </div>
    </div>

    <div style="display: grid; gap: 1.25rem; max-width: 880px;">

        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="panel stack">
            @csrf
            @method('PUT')

            <div class="panel-header" style="margin-bottom: 0.25rem;">
                <div>
                    <h2>Profile Information</h2>
                    <p>Update your name, email and profile picture.</p>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 1.25rem; padding: 1rem; background: var(--surface-2); border: 1px solid var(--line); border-radius: var(--radius-sm);">
                @if ($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #fff; box-shadow: var(--shadow-sm);">
                @else
                    <span style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #22d3ee); color: #fff; display: grid; place-items: center; font-weight: 800; font-size: 1.8rem;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                @endif
                <div style="flex: 1; display: grid; gap: 0.65rem;">
                    <label>
                        Upload new picture
                        <input type="file" name="avatar" accept="image/*">
                    </label>
                    @if ($user->avatar_path && ! str_starts_with((string) $user->avatar_path, 'http'))
                        <label class="check-row">
                            <input type="checkbox" name="remove_avatar" value="1">
                            <span style="color: var(--danger); font-weight: 600;">Remove current picture</span>
                        </label>
                    @endif
                </div>
            </div>

            <div class="form-grid">
                <label>
                    Full Name
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                </label>
                <label>
                    Email
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                </label>
                <label>
                    Phone
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+1 555 0100">
                </label>
                <label>
                    Address
                    <input type="text" name="address" value="{{ old('address', $user->address) }}" placeholder="Street, City, Country">
                </label>
            </div>

            <div class="form-actions" style="justify-content: flex-end;">
                <button class="btn btn-primary" type="submit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M5 12l4 4L19 6"/></svg>
                    Save Profile
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.profile.password') }}" class="panel stack">
            @csrf
            @method('PUT')

            <div class="panel-header" style="margin-bottom: 0.25rem;">
                <div>
                    <h2>Change Password</h2>
                    <p>Use a strong password you don't reuse anywhere else.</p>
                </div>
            </div>

            <label>
                Current Password
                <input type="password" name="current_password" autocomplete="current-password" placeholder="Your current password">
            </label>
            <div class="form-grid">
                <label>
                    New Password
                    <input type="password" name="password" autocomplete="new-password" placeholder="At least 6 characters">
                </label>
                <label>
                    Confirm New Password
                    <input type="password" name="password_confirmation" autocomplete="new-password" placeholder="Repeat the new password">
                </label>
            </div>

            <div class="form-actions" style="justify-content: flex-end;">
                <button class="btn btn-primary" type="submit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"/></svg>
                    Update Password
                </button>
            </div>
        </form>
    </div>
@endsection
