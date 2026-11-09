<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar - Hoshist</title>
    <link rel="stylesheet" href="{{ asset('css/hoshist.css') }}">
</head>
<body>
<div class="login-wrap">
    <form class="login-card" method="POST" action="{{ route('register') }}">
        @csrf
        <h1 class="login-title">Daftar di Hoshist</h1>

        @if ($errors->any())
            <div class="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <label for="name">Nama</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>

        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="{{ old('username') }}" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <label for="password_confirmation">Ulangi password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required>

        <button type="submit" class="btn-gold">Daftar</button>

        <p class="login-link">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
    </form>
</div>
</body>
</html>