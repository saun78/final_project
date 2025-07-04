<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    // 显示登录表单
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // 处理登录逻辑
    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        // 尝试登录
        if (Auth::attempt(['username' => $username, 'password' => $password])) {
            $request->session()->regenerate();
            return redirect()->route('login')->with('success', 'Login successful!');
        }

        // 用户是否存在
        $user = User::where('username', $username)->first();
        if (!$user) {
            return back()->withErrors(['username' => 'Username is incorrect.'])->withInput();
        }

        // 密码是否正确
        if (!password_verify($password, $user->password)) {
            return back()->withErrors(['password' => 'Password is incorrect.'])->withInput();
        }

        // 两者都错（极少触发，因为上面已经判断）
        return back()->withErrors(['login' => 'User not found.'])->withInput();
    }

    // 登出逻辑
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('logout_success', 'You have been logged out successfully.');
    }
}
