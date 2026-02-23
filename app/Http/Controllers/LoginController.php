<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie; // Tambahkan ini

class LoginController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // Ambil email dari cookie jika ada
        $rememberedEmail = Cookie::get('remembered_email');

        return view('be.login', compact('rememberedEmail'));
    }

    public function process(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Logika Remember Me (Laravel Built-in)
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // LOGIKA COOKIE UNTUK EMAIL:
            if ($remember) {
                // Simpan email selama 30 hari (43200 menit)
                Cookie::queue('remembered_email', $request->email, 43200);
            } else {
                // Hapus cookie jika tidak dicentang
                Cookie::queue(Cookie::forget('remembered_email'));
            }

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}