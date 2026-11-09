@extends('layouts.app')

@section('title', $title->title)

@section('content')
    @php
        $colors = ['#3B3F8F', '#2F6F8F', '#A8486B', '#6B4BA8', '#3F7F6B'];
        $avg = $stats->avg_score !== null ? number_format((float) $stats->avg_score, 1) : '-';
    @endphp

    <div class="back-bar">
        <a href="{{ route('explore') }}" class="back-link" aria-label="Kembali ke Jelajahi">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
        </a>
        <h1 class="back-title">{{ $title->title }}</h1>
    </div>

    @if (session('status'))
        <div class="flash">{{ session('status') }}</div>
    @endif

    <div class="detail-head">
        <div class="po detail-poster" style="background: {{ $colors[$title->id % 5] }}">{{ strtoupper($title->type) }}</div>

        <div class="detail-info">
            <div class="m">{{ ucfirst($title->type) }} &middot; {{ $title->release_year }}</div>

            <div class="detail-score">
                <b>{{ $avg }}</b>
                <span class="m">dari {{ $stats->total }} penilaian</span>
            </div>

            @if ($genres->isNotEmpty())
                <div class="tags">
                    @foreach ($genres as $g)
                        <span class="tg">{{ $g }}</span>
                    @endforeach
                </div>
            @endif

            @if ($title->synopsis)
                <p class="detail-syn">{{ $title->synopsis }}</p>
            @endif
        </div>
    </div>

    <div class="rate-box">
        <h2>{{ $myRating ? 'Rating kamu' : 'Beri penilaianmu' }}</h2>
        @include('partials.rating-form', ['titleId' => $title->id, 'myRating' => $myRating])
    </div>

    <div class="section-title">Review pengguna</div>

    @forelse ($reviews as $r)
        <article class="post">
            <div class="av">{{ strtoupper(mb_substr($r->name, 0, 1)) }}</div>
            <div class="post-body">
                <div class="post-head">
                    <b>{{ $r->name }}</b>
                    <span class="m">&#64;{{ $r->username }} &middot; {{ \Carbon\Carbon::parse($r->updated_at)->locale('id')->diffForHumans() }}</span>
                    <span class="r">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="#F5C451" aria-hidden="true"><path d="M12 2l3 6.9 7.5.7-5.7 5 1.7 7.4L12 18l-6.5 4 1.7-7.4-5.7-5 7.5-.7z"/></svg>{{ $r->score }}
                    </span>
                </div>
                <p class="post-text">{{ $r->review }}</p>
            </div>
        </article>
    @empty
        <div class="empty">Belum ada review. Jadilah yang pertama!</div>
    @endforelse
@endsection