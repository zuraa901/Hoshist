@php
    // Data contoh (sementara). Nanti diganti dengan data dari database.
    $trending = [
        ['Anime', 'Langit Terakhir', '1,2 rb rating'],
        ['Manga', 'Surat untuk Hoshi', '860 rating'],
        ['Drama', 'Musim Kedua Kita', '640 rating'],
        ['Film', 'Kota Senja', '512 rating'],
    ];
    $friends = [
        ['R', 'Raka', 3],
        ['S', 'Sinta', 2],
        ['B', 'Bayu', 2],
    ];
@endphp

@guest
    <div class="cd">
        <h2>Gabung Hoshist</h2>
        <p class="m">Masuk atau daftar untuk memberi rating, menulis review, dan mencari teman sehobi.</p>
        <a href="{{ route('login') }}" class="btn-main">Masuk</a>
        <a href="{{ route('register') }}" class="btn-out">Daftar</a>
    </div>
@endguest

<div class="cd">
    <h2>Trending minggu ini</h2>
    @foreach ($trending as $item)
        <div>
            <div class="m">{{ $item[0] }} &middot; Trending</div>
            <div class="strong">{{ $item[1] }}</div>
            <div class="m">{{ $item[2] }}</div>
        </div>
    @endforeach
</div>

<div class="cd">
    <h2>Teman sehobi</h2>
    @foreach ($friends as $friend)
        <div class="friend">
            <div class="av">{{ $friend[0] }}</div>
            <div class="friend-info">
                <div class="strong">{{ $friend[1] }}</div>
                <div class="m">{{ $friend[2] }} genre sama</div>
            </div>
            @auth
                <button type="button" class="fb">Ikuti</button>
            @else
                <a href="{{ route('login') }}" class="fb">Ikuti</a>
            @endauth
        </div>
    @endforeach
</div>

