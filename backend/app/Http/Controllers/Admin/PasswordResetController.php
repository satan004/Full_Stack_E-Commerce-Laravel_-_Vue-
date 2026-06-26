<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function showForgot(): View
    {
        return view('admin.password.email');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($data, function (User $user, string $token): void {
            $resetUrl = route('admin.password.reset', [
                'token' => $token,
                'email' => $user->email,
            ]);

            Mail::raw(
                "Hello {$user->name},\n\nWe received a request to reset your admin password.\nClick the link below to choose a new password:\n\n{$resetUrl}\n\nIf you did not request this, you can safely ignore this email.\n\n— Commerce Admin",
                function ($message) use ($user): void {
                    $message->to($user->email, $user->name)
                        ->subject('Reset your admin password');
                }
            );
        });

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
    }

    public function showReset(Request $request, string $token): View
    {
        return view('admin.password.reset', [
            'token' => $token,
            'email' => $request->query('email', old('email')),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $status = Password::reset($data, function (User $user, string $password): void {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withErrors(['email' => __($status)])
                ->withInput($request->only('email'));
        }

        // Auto-login the admin after reset if the email belongs to an admin
        $user = User::where('email', $data['email'])->first();
        if ($user && $user->is_admin) {
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('admin.dashboard')->with('status', 'Your password has been reset and you are now signed in.');
        }

        return redirect()->route('admin.login')->with('status', __($status));
    }

    /**
     * Lightweight fallback when no mail driver is configured:
     * writes a token to the cache and shows the reset URL on screen
     * (only used in local dev / when MAIL_MAILER=log).
     */
    public function devLink(string $email): ?string
    {
        if (! app()->environment('local')) {
            return null;
        }

        $token = Password::broker()->createToken(User::where('email', $email)->first());

        return $token ? route('admin.password.reset', ['token' => $token, 'email' => $email]) : null;
    }
}
