<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Beranda') - Hoshist</title>
    <link rel="stylesheet" href="{{ asset('css/hoshist.css') }}">
</head>
<body>
@php
    $loggedIn = auth()->check();
@endphp

<div class="shell">

    <aside class="lf">
        <nav class="menu">
            <a href="{{ route('home') }}" class="ni {{ request()->routeIs('home') ? 'on' : '' }}">Beranda</a>
            <a href="{{ $loggedIn ? route('explore') : route('login') }}" class="ni {{ request()->routeIs('explore') ? 'on' : '' }}">Jelajahi</a>
            <a href="{{ $loggedIn ? '#' : route('login') }}" class="ni">Teman</a>
            <a href="{{ $loggedIn ? route('profile') : route('login') }}" class="ni {{ request()->routeIs('profile') ? 'on' : '' }}">Profil</a>
        </nav>

        @auth
            <a href="{{ route('rating.create') }}" class="btn-main">Beri Rating</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-out">Keluar ({{ auth()->user()->username }})</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn-main">Beri Rating</a>
        @endauth
    </aside>

    <main class="ct">
        @yield('content')
    </main>

    <aside class="rt">
        @include('partials.rightPanel')
    </aside>

</div>
</body>
</html>