<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use NotificationChannels\Expo\ExpoPushToken;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google for authentication
     */
    public function redirectToGoogle(Request $request)
    {
        if ($request->filled('expo_token')) {
            session(['expo_token' => $request->expo_token]);
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the Google callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Find or create user
            $user = User::where('email', $googleUser->getEmail())->first();
            logger()->info('User found', ['user' => $user]);
            if ($user) {
                // Update google_id if not set
                if (! $user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => Hash::make(Str::random(24)), // Random password
                    'email_verified_at' => now(),
                ]);
            }

            // Log the user in
            Auth::login($user, true);

            if (session()->has('expo_token')) {
                $token = session()->pull('expo_token');
                $validator = Validator::make(['expo_token' => $token], [
                    'expo_token' => ExpoPushToken::rule(),
                ]);

                if ($validator->passes()) {
                    $user->update(['expo_token' => $token]);
                }
            }

            if (! $user->hasCompletedAssessment() && ! $user->isAdmin()) {
                return redirect()->route('assessment.index');
            }

            return redirect()->intended(route('dashboard', absolute: false));

        } catch (\Exception $e) {
            logger()->error('Google login failed', ['error' => $e]);

            return redirect()->route('login')->with('error', 'Unable to login with Google. Please try again.');
        }
    }
}
