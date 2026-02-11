<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{
    public function index(){
        return view('auth.login') ; 
    }

    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // توجيه المستخدم حسب رتبته
            $role = Auth::user()->role;
            if ($role === 'admin') return redirect('/dashboard');
            if ($role === 'acountant') return redirect('/payments');
            if ($role === 'seller') return redirect('/units') ; 
            return redirect('/dashboard');
        }

        return back()->withErrors(['email'=>'البيانات المدخلة غير صحيحة']) ;
    }

    public function logout(Request $request) {
        Auth::logout(); 
        $request->session()->invalidate(); 
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
