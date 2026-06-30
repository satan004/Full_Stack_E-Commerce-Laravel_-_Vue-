<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\IssuesApiTokens;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    use IssuesApiTokens;

    public function redirect(): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return $this->frontendError('Google login is not configured.');
        }

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->stateless()
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return $this->frontendError($this->googleErrorMessage(
                $request->string('error')->toString(),
                $request->string('error_description')->toString(),
            ));
        }

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (Throwable $e) {
            Log::warning('Google OAuth callback failed.', [
                'message' => $e->getMessage(),
                'redirect_uri' => config('services.google.redirect'),
                'has_code' => $request->filled('code'),
            ]);

            return $this->frontendError('Google login failed. Please check the Google OAuth redirect URI.');
        }

        $googleId = (string) $googleUser->getId();
        $email = $googleUser->getEmail();

        if ($googleId === '') {
            return $this->frontendError('Google did not return a valid account ID.');
        }

        if (! $email) {
            return $this->frontendError('Google did not return an email address.');
        }

        $user = User::where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            $updates = [
                'email_verified_at' => $user->email_verified_at ?? now(),
            ];

            if (! $user->google_id) {
                $updates['google_id'] = $googleId;
            }

            if (! $user->avatar_path && $googleUser->getAvatar()) {
                $updates['avatar_path'] = $googleUser->getAvatar();
            }

            $user->forceFill($updates)->save();
        } else {
            $user = User::create([
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: Str::before($email, '@'),
                'email' => $email,
                'google_id' => $googleId,
                'avatar_path' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
                'password' => Str::random(40),
            ]);
        }

        return $this->frontendSuccess($this->issueToken($user));
    }

    private function frontendSuccess(string $token): RedirectResponse
    {
        $query = http_build_query(['oauth' => 'google'], '', '&', PHP_QUERY_RFC3986);
        $fragment = http_build_query([
            'token' => $token,
            'redirect' => '/dashboard',
        ], '', '&', PHP_QUERY_RFC3986);

        return redirect()->away($this->frontendUrl('/login') . '?' . $query . '#' . $fragment);
    }

    private function frontendError(string $message): RedirectResponse
    {
        $query = http_build_query(['google_error' => $message], '', '&', PHP_QUERY_RFC3986);

        return redirect()->away($this->frontendUrl('/login') . '?' . $query);
    }

    private function googleErrorMessage(string $error, string $description): string
    {
        if ($error === 'access_denied') {
            return 'Google login was cancelled.';
        }

        if ($description !== '') {
            return "Google login failed: {$description}";
        }

        return "Google login failed: {$error}";
    }

    private function frontendUrl(string $path): string
    {
        $baseUrl = rtrim((string) config('services.frontend.url', config('app.url')), '/');

        return $baseUrl . '/' . ltrim($path, '/');
    }
}
