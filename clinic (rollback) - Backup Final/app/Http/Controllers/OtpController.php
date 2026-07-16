<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    public function show()
    {
        $user = auth()->user();

        // If OTP is already verified, redirect based on role
        if ($user->is_otp_verified) {
            return redirect()->route(match ($user->role) {
                'admin' => 'admin.dashboard',
                'user' => 'user.dashboard',
                default => 'dashboard',
            });
        }

        // Otherwise, show the OTP form
        return view('auth.otp-verify');
    }

    public function verify(Request $request)
    {
        $request->validate(['otp' => 'required|numeric']);

        $user = auth()->user();

        if (
            $user->otp_code === $request->otp &&
            now()->lt($user->otp_expires_at)
        ) {
            $user->is_otp_verified = true;
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->save();

            return redirect()->intended(match ($user->role) {
                'admin' => route('admin.dashboard'),
                'user' => route('user.dashboard'),
                default => '/dashboard',
            });
        }

        return back()->withErrors(['otp' => 'Invalid or expired OTP']);
    }

    public function cancel(Request $request)
    {
        $user = auth()->user();

        // Clear OTP-related fields
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->is_otp_verified = false;
        $user->save();

        Auth::logout(); // Log user out

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'OTP verification canceled. Please log in again.');
    }

}
