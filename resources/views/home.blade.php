@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    @php
        $loggedIn = auth()->check();
        $tabs = [
            'timeline'    => 'Timeline',
            'mengikuti'   => 'Mengikuti',
            'rekomendasi' => 'Rekomendasi',
            'trending'    => 'Trending',
        ];
        $types = [
            'semua' => 'Semua',
            'anime' => 'Anime',
            'film'  => 'Film',
            'drama' => 'Drama',
            'manga' => 'Manga',
            'komik' => 'Komik',
        ];
        $colors = ['#3B3F8F', '#2F6F8F', '#A8486B', '#6B4BA8', '#3F7F6B'];
    @endphp

    <div class="tabs">
        @foreach ($tabs as $key => $label)
            <a href="{{ ($loggedIn || $key === 'timeline') ? route('home', ['tab' => $key]) : route('login') }}"
               class="tab {{ $tab === $key ? 'on' : '' }}"><span class="tl">{{ $label }}</span></a>
        @endforeach
    </div>

    @if (session('status'))
        <div class="flash">{{ session('status') }}</div>
    @endif

    @if ($tab === 'rekomendasi')
        <div class="rec-head">
            <h2>Rekomendasi untukmu</h2>
            <div class="m">Judul yang paling cocok dengan genre favoritmu tampil paling atas.</div>
        </div>

        <div class="chip-row">
            @foreach ($types as $key => $label)
                <a href="{{ route('home', ['tab' => 'rekomendasi', 'type' => $key]) }}"
                   class="ch {{ $type === $key ? 'on' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        @forelse ($titles as $t)
            <div class="rec-item">
                <div class="po rec-poster" style="background: {{ $colors[$t->id % 5] }}">{{ strtoupper($t->type) }}</div>

                <div class="rec-info">
                    <div class="strong">{{ $t->title }}</div>
                    <div class="m">{{ ucfirst($t->type) }} &middot; {{ $t->release_year }}</div>

                    @if (count($t->genres) > 0)
                        <div class="tags">
                            @foreach ($t->genres as $genre)
                                <span class="tg">{{ $genre }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if ($t->synopsis)
                        <div class="rec-syn">{{ $t->synopsis }}</div>
                    @endif
                </div>

                <div class="rec-side">
                    <span class="r">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="#F5C451" aria-hidden="true"><path d="M12 2l3 6.9 7.5.7-5.7 5 1.7 7.4L12 18l-6.5 4 1.7-7.4-5.7-5 7.5-.7z"/></svg>{{ $t->avg_score !== null ? number_format($t->avg_score, 1) : '-' }}
                    </span>
                    @if ($t->match !== null && $t->match > 0)
                        <span class="match">{{ $t->match }}% cocok</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty">Belum ada judul untuk ditampilkan.</div>
        @endforelse

    @elseif ($tab === 'timeline')
        {{-- Kotak tulis postingan (hanya untuk yang sudah login) --}}
        @auth
            <form method="POST" action="{{ route('post.store') }}" class="composer">
                @csrf
                <div class="av">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="composer-main">
                    <textarea name="body" class="composer-input" rows="2" maxlength="500" placeholder="Apa yang baru kamu tonton atau baca?" aria-label="Tulis postingan">{{ old('body') }}</textarea>

                    @error('body')
                        <div class="alert">{{ $message }}</div>
                    @enderror

                    <div class="composer-foot">
                        <a href="{{ route('rating.create') }}" class="composer-link">Beri rating</a>
                        <button type="submit" class="btn-gold">Posting</button>
                    </div>
                </div>
            </form>
        @endauth

        {{-- Isi timeline --}}
        @forelse ($feed as $item)
            <article class="post">
                <div class="av">{{ strtoupper(mb_substr($item->name, 0, 1)) }}</div>

                <div class="post-body">
                    <div class="post-head">
                        <b>{{ $item->name }}</b>
                        <span class="m">&#64;{{ $item->username }} &middot; {{ \Carbon\Carbon::parse($item->posted_at)->locale('id')->diffForHumans() }}</span>
                    </div>

                    @if ($item->title_name)
                        <div class="post-about m">
                            {{ $item->kind === 'rating' ? 'menilai' : 'membahas' }}
                            @if ($loggedIn)
                                <a href="{{ route('detail.show', $item->title_id) }}" class="title-link">{{ $item->title_name }}</a>
                            @else
                                <b>{{ $item->title_name }}</b>
                            @endif

                            @if ($item->kind === 'rating')
                                <span class="r">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="#F5C451" aria-hidden="true"><path d="M12 2l3 6.9 7.5.7-5.7 5 1.7 7.4L12 18l-6.5 4 1.7-7.4-5.7-5 7.5-.7z"/></svg>{{ $item->score }}
                                </span>
                            @endif
                        </div>
                    @endif

                    @if ($item->body)
                        <p class="post-text">{{ $item->body }}</p>
                    @endif
                </div>
            </article>
        @empty
            <div class="empty">Belum ada postingan. Jadilah yang pertama memberi rating!</div>
        @endforelse

    @else
        <div class="empty">Segera hadir.</div>
    @endif
@endsection