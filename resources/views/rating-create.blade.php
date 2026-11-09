@extends('layouts.app')

@section('title', 'Beri Rating')

@section('content')
    <div class="back-bar">
        <a href="{{ route('home') }}" class="back-link" aria-label="Kembali ke Beranda">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
        </a>
        <h1 class="back-title">Beri Rating</h1>
    </div>

    <div class="rate-box rate-box-top">
        <p class="m">Ketik judul yang baru kamu tonton atau baca, pilih dari daftar yang muncul, beri skor, lalu tulis review singkat. Kalau judulnya sudah pernah kamu nilai, rating lamamu akan diperbarui.</p>
        @include('partials.rating-form', ['selectedTitle' => $selectedTitle])
    </div>
@endsection