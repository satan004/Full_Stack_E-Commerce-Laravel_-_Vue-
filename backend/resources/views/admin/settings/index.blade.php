@extends('layouts.admin')

@section('title', 'General Settings')

@section('content')
    <div class="admin-page-header">
        <div>
            <p class="eyebrow">Account</p>
            <h1>General Settings</h1>
            <p>Customize your store identity, branding and contact details.</p>
        </div>
    </div>

    <div style="display: grid; gap: 1.25rem; max-width: 980px;">

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="panel stack">
            @csrf
            @method('POST')

            <div class="panel-header" style="margin-bottom: 0.25rem;">
                <div>
                    <h2>Site Identity</h2>
                    <p>How your store appears across the admin panel and storefront.</p>
                </div>
            </div>

            <div class="form-grid">
                <label>
                    Site Name
                    <input type="text" name="site_name" value="{{ old('site_name', $settings['site_name']) }}" required>
                </label>
                <label>
                    Tagline
                    <input type="text" name="site_tagline" value="{{ old('site_tagline', $settings['site_tagline']) }}" placeholder="Short tagline">
                </label>
            </div>

            <label>
                Description
                <textarea name="site_description" rows="3" placeholder="A short description of your store...">{{ old('site_description', $settings['site_description']) }}</textarea>
            </label>

            <div class="form-grid">
                <label>
                    Contact Email
                    <input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}">
                </label>
                <label>
                    Contact Phone
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone']) }}">
                </label>
                <label>
                    Currency
                    <input type="text" name="currency" value="{{ old('currency', $settings['currency']) }}" placeholder="USD">
                </label>
                <label>
                    Primary Color
                    <input type="color" name="primary_color" value="{{ old('primary_color', $settings['primary_color']) }}" style="height: 44px; padding: 0.3rem;">
                </label>
            </div>

            <div class="form-grid">
                <div>
                    <label>
                        Website Logo
                        <input type="file" name="logo" accept="image/*">
                    </label>
                    @if (! empty($settings['logo_path']))
                        <div style="margin-top: 0.6rem; display: flex; align-items: center; gap: 0.75rem;">
                            <img src="{{ asset('storage/' . $settings['logo_path']) }}" alt="Logo" style="width: 56px; height: 56px; object-fit: cover; border-radius: 10px; background: #fff; border: 1px solid var(--line); padding: 4px;">
                            <small style="color: var(--muted);">Current logo</small>
                        </div>
                    @endif
                </div>
                <div>
                    <label>
                        Favicon
                        <input type="file" name="favicon" accept=".ico,.png,.svg,image/*">
                    </label>
                    @if (! empty($settings['favicon_path']))
                        <div style="margin-top: 0.6rem; display: flex; align-items: center; gap: 0.75rem;">
                            <img src="{{ asset('storage/' . $settings['favicon_path']) }}" alt="Favicon" style="width: 32px; height: 32px; object-fit: contain; border-radius: 6px; background: #fff; border: 1px solid var(--line); padding: 2px;">
                            <small style="color: var(--muted);">Current favicon</small>
                        </div>
                    @endif
                </div>
            </div>

            <div class="form-actions" style="justify-content: flex-end;">
                <button class="btn btn-primary" type="submit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M5 12l4 4L19 6"/></svg>
                    Save Settings
                </button>
            </div>
        </form>

        <div class="panel stack">
            <div class="panel-header" style="margin-bottom: 0.25rem;">
                <div>
                    <h2>Tips</h2>
                    <p>Helpful pointers for getting the most out of these settings.</p>
                </div>
            </div>
            <ul style="margin: 0; padding-left: 1.1rem; color: var(--muted); line-height: 1.7;">
                <li>The site name appears in the admin sidebar and browser title.</li>
                <li>Upload a square logo (PNG/SVG) for best results across screens.</li>
                <li>Use a 32×32 ICO or PNG for the favicon.</li>
                <li>The primary color is currently a brand accent — rebrand later by editing <code>resources/css/admin.css</code>.</li>
            </ul>
        </div>
    </div>
@endsection
