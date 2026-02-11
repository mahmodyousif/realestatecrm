<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function index(){
        $users = User::all() ; 
        return view('users' , compact('users')) ; 
    }

    public function store(Request $request) {

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'user',
        ]);

        return redirect()->back()->with('success', 'تم تسجيل المستخدم بنجاح.');

    }

    public function update(Request $request, User $user)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'role' => 'required|string',
        'password' => 'nullable|min:6',
    ]);

    $user->name  = $request->name;
    $user->email = $request->email;
    $user->role  = $request->role;

    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    return redirect()->back()->with('success', 'تم تحديث بيانات المستخدم بنجاح');
}

public function destroy(User $user)
{
    // منع حذف نفسك (اختياري لكنه احترافي)
    if (auth()->id() === $user->id) {
        return redirect()->back()->with('error', 'لا يمكنك حذف حسابك');
    }

    $user->delete();

    return redirect()->back()->with('success', 'تم حذف المستخدم بنجاح');
}

}

