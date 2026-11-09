<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(
            [
                'name'     => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'min:3', 'max:30', 'alpha_dash', 'unique:users,username'],
                'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ],
            [
                'required'            => 'Kolom :attribute wajib diisi.',
                'username.min'        => 'Username minimal 3 karakter.',
                'username.alpha_dash' => 'Username hanya boleh huruf, angka, strip, dan garis bawah.',
                'username.unique'     => 'Username sudah dipakai.',
                'email.email'         => 'Format email tidak valid.',
                'email.unique'        => 'Email sudah terdaftar.',
                'password.min'        => 'Password minimal 8 karakter.',
                'password.confirmed'  => 'Konfirmasi password tidak cocok.',
            ],
            [
                'name'     => 'nama',
                'username' => 'username',
                'email'    => 'email',
                'password' => 'password',
            ]
        );

        $user = new User();
        $user->name = $data['name'];
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home');
    }
}