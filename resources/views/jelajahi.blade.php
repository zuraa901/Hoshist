@extends('layouts.app')

@section('title', 'Jelajahi')

@section('content')
    @php
        // Parameter pencarian yang sedang aktif (nilai bawaan tidak ditulis di alamat)
        $params = array_filter([
            'q'     => $q,
            'tab'   => $tab === 'pengguna' ? 'pengguna' : null,
            'type'  => $type !== 'semua' ? $type : null,
            'genre' => $genre !== '' ? $genre : null,
            'sort'  => $sort !== 'rating' ? $sort : null,
        ], fn ($v) => $v !== null && $v !== '');

        // Membuat alamat baru dengan mengganti sebagian parameter
        $link = function (array $changes) use ($params) {
            return route('explore', array_filter(
                array_merge($params, $changes),
                fn ($v) => $v !== null && $v !== ''
            ));
        };

        $types = ['semua' => 'Semua', 'anime' => 'Anime', 'film' => 'Film', 'drama' => 'Drama', 'manga' => 'Manga', 'komik' => 'Komik'];
        $sorts = ['rating' => 'Rating tertinggi', 'terbaru' => 'Terbaru', 'populer' => 'Paling banyak dinilai'];
        $colors = ['#3B3F8F', '#2F6F8F', '#A8486B', '#6B4BA8', '#3F7F6B'];
    @endphp

    {{-- Kolom cari --}}
    <form method="GET" action="{{ route('explore') }}" class="xsearch">
        @foreach (array_diff_key($params, ['q' => 1]) as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
        <input type="search" name="q" value="{{ $q }}" aria-label="Cari judul atau pengguna" placeholder="Cari judul atau pengguna" autofocus>
        <button type="submit" class="xsearch-btn" aria-label="Cari">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
        </button>
    </form>

    {{-- Tab Judul / Pengguna --}}
    <div class="tabs">
        <a href="{{ $link(['tab' => null]) }}" class="tab {{ $tab === 'judul' ? 'on' : '' }}"><span class="tl">Judul</span></a>
        <a href="{{ $link(['tab' => 'pengguna']) }}" class="tab {{ $tab === 'pengguna' ? 'on' : '' }}"><span class="tl">Pengguna</span></a>
    </div>

    {{-- Filter --}}
    @if ($tab === 'judul')
        <div class="filter-row">
            <span class="m filter-label">Tipe</span>
            @foreach ($types as $key => $label)
                <a href="{{ $link(['type' => $key === 'semua' ? null : $key]) }}" class="ch {{ $type === $key ? 'on' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
    @endif

    @if (count($genres) > 0)
        <div class="filter-row">
            <span class="m filter-label">Genre</span>
            <a href="{{ $link(['genre' => null]) }}" class="ch {{ $genre === '' ? 'on' : '' }}">Semua</a>
            @foreach ($genres as $g)
                <a href="{{ $link(['genre' => $g]) }}" class="ch {{ $genre === $g ? 'on' : '' }}">{{ $g }}</a>
            @endforeach
        </div>
    @endif

    @if ($tab === 'judul')
        <div class="filter-row">
            <span class="m filter-label">Urutkan</span>
            @foreach ($sorts as $key => $label)
                <a href="{{ $link(['sort' => $key === 'rating' ? null : $key]) }}" class="ch {{ $sort === $key ? 'on' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
    @endif

    {{-- Hasil --}}
    @if (! $searched)
        <div class="empty">
            Ketik judul atau username untuk mulai mencari.
            Awali dengan &#64; untuk mencari pengguna.
        </div>

    @elseif ($tab === 'pengguna')
        @forelse ($results as $u)
            <div class="rec-item">
                <div class="av">{{ strtoupper(mb_substr($u->name, 0, 1)) }}</div>

                <div class="rec-info">
                    <div>
                        <b>{{ $u->name }}</b>
                        <span class="m">&#64;{{ $u->username }}</span>
                    </div>

                    @if (count($u->genres) > 0)
                        <div class="tags">
                            @foreach ($u->genres as $g)
                                <span class="tg">{{ $g }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="m">{{ $u->shared }} genre sama denganmu</div>
                </div>
            </div>
        @empty
            <div class="empty">Tidak ada pengguna yang cocok dengan pencarianmu.</div>
        @endforelse

    @else
        @forelse ($results as $t)
            @php
                // Link ke detail judul (jadi '#' selama routenya belum dibuat)
                $detailUrl = \Illuminate\Support\Facades\Route::has('detail.show')
                    ? route('detail.show', $t->id)
                    : '#';
            @endphp

            <a href="{{ $detailUrl }}" class="rec-item rec-item-link" aria-label="Lihat detail {{ $t->title }}">
                <div class="po rec-poster" style="background: {{ $colors[$t->id % 5] }}">{{ strtoupper($t->type) }}</div>

                <div class="rec-info">
                    <div class="strong">{{ $t->title }}</div>
                    <div class="m">{{ ucfirst($t->type) }} &middot; {{ $t->release_year }}</div>

                    @if (count($t->genres) > 0)
                        <div class="tags">
                            @foreach ($t->genres as $g)
                                <span class="tg">{{ $g }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="rec-side">
                    <span class="r">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="#F5C451" aria-hidden="true"><path d="M12 2l3 6.9 7.5.7-5.7 5 1.7 7.4L12 18l-6.5 4 1.7-7.4-5.7-5 7.5-.7z"/></svg>{{ $t->avg_score !== null ? number_format($t->avg_score, 1) : '-' }}
                    </span>
                    <span class="rec-click-hint">Lihat detail &rarr;</span>
                </div>
            </a>
        @empty
            <div class="empty">Tidak ada judul yang cocok dengan pencarianmu.</div>
        @endforelse
    @endif
@endsection