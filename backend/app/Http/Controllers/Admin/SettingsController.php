<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'settings' => Setting::allCached(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:120'],
            'site_tagline' => ['nullable', 'string', 'max:160'],
            'site_description' => ['nullable', 'string', 'max:500'],
            'contact_email' => ['nullable', 'email'],
            'contact_phone' => ['nullable', 'string', 'max:60'],
            'currency' => ['required', 'string', 'max:8'],
            'primary_color' => ['required', 'string', 'max:16'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:ico,png,svg', 'max:1024'],
        ]);

        $current = Setting::allCached();

        if ($request->hasFile('logo')) {
            $this->deleteOldFile($current['logo_path'] ?? null);
            $data['logo_path'] = $request->file('logo')->store('settings', 'public');
        }

        if ($request->hasFile('favicon')) {
            $this->deleteOldFile($current['favicon_path'] ?? null);
            $data['favicon_path'] = $request->file('favicon')->store('settings', 'public');
        }

        unset($data['logo'], $data['favicon']);

        Setting::putMany($data);

        return back()->with('status', 'General settings saved successfully.');
    }

    protected function deleteOldFile(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }
}
