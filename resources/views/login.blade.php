<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Hoshist</title>
    <link rel="stylesheet" href="{{ asset('css/hoshist.css') }}">
</head>
<body>
<div class="login-wrap">
    <form class="login-card" method="POST" action="{{ route('login') }}">
        @csrf
        <h1 class="login-title">Masuk ke Hoshist</h1>

        @if ($errors->any())
            <div class="alert">{{ $errors->first() }}</div>
        @endif

        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" class="btn-gold">Masuk</button>

        <p class="login-link">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
    </form>
</div>
</body>
</html>