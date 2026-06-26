<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function defaults(): array
    {
        return [
            'site_name' => 'Commerce',
            'site_tagline' => 'Laravel API + Vue storefront',
            'site_description' => 'A modern e-commerce platform built on Laravel.',
            'logo_path' => null,
            'favicon_path' => null,
            'contact_email' => 'support@commerce.test',
            'contact_phone' => '+1 555 0100',
            'currency' => 'USD',
            'primary_color' => '#4f46e5',
        ];
    }

    public static function allCached(): array
    {
        return Cache::rememberForever('site_settings', function (): array {
            $stored = static::pluck('value', 'key')->toArray();

            return array_merge(static::defaults(), $stored);
        });
    }

    public static function get(string $key, mixed $fallback = null): mixed
    {
        $settings = static::allCached();

        return $settings[$key] ?? $fallback;
    }

    public static function put(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('site_settings');
    }

    public static function putMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget('site_settings');
    }
}
