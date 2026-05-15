<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $configuredPassword = (string) env('ADMIN_PASSWORD', 'admin123');
        $matches = str_starts_with($configuredPassword, '$2y$')
            ? Hash::check($data['password'], $configuredPassword)
            : hash_equals($configuredPassword, $data['password']);

        if (! $matches) {
            return back()->withErrors(['password' => 'The admin password is incorrect.']);
        }

        $request->session()->regenerate();
        $request->session()->put('admin_authenticated', true);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget('admin_authenticated');
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
