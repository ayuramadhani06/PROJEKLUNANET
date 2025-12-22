<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function index()
    {
        return view('be.login');
    }

    public function process(Request $request)
    {
        // dummy login dulu
        if ($request->email === 'admin@mail.com' && $request->password === 'admin') {
            session(['logged_in' => true]);
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah'
        ]);
    }

    public function logout()
    {
        session()->forget('logged_in');
        return redirect()->route('login');
    }
}
