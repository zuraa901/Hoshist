<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $tab  = $request->query('tab', 'timeline');
        $type = $request->query('type', 'semua');

        if (! in_array($tab, ['timeline', 'mengikuti', 'rekomendasi', 'trending'], true)) {
            $tab = 'timeline';
        }

        if (! in_array($type, ['semua', 'anime', 'film', 'drama', 'manga', 'komik'], true)) {
            $type = 'semua';
        }

        // Pengunjung hanya boleh melihat timeline
        if (! Auth::check() && $tab !== 'timeline') {
            return redirect()->route('login');
        }

        $titles = collect();
        $feed   = collect();

        if ($tab === 'rekomendasi') {
            $titles = $this->recommendations($type);
        }

        if ($tab === 'timeline') {
            $feed = $this->timeline();
        }

        return view('home', compact('tab', 'type', 'titles', 'feed'));
    }

    // Timeline = rating (dengan atau tanpa review) + postingan teks, yang terbaru di atas
    private function timeline(): Collection
    {
        $ratings = DB::table('ratings')
            ->join('users', 'users.id', '=', 'ratings.user_id')
            ->join('titles', 'titles.id', '=', 'ratings.title_id')
            ->select(
                DB::raw("'rating' as kind"),
                'ratings.id as item_id',
                'users.name',
                'users.username',
                'titles.id as title_id',
                'titles.title as title_name',
                'ratings.score',
                'ratings.review as body',
                'ratings.updated_at as posted_at'
            );

        $posts = DB::table('posts')
            ->join('users', 'users.id', '=', 'posts.user_id')
            ->leftJoin('titles', 'titles.id', '=', 'posts.title_id')
            ->select(
                DB::raw("'post' as kind"),
                'posts.id as item_id',
                'users.name',
                'users.username',
                'titles.id as title_id',
                'titles.title as title_name',
                DB::raw('NULL as score'),
                'posts.body as body',
                'posts.created_at as posted_at'
            );

        return DB::query()
            ->fromSub($ratings->unionAll($posts), 'feed')
            ->orderByDesc('posted_at')
            ->limit(30)
            ->get();
    }

    private function recommendations(string $type): Collection
    {
        // Daftar judul beserta rata-rata ratingnya
        $query = DB::table('titles')
            ->leftJoin('ratings', 'ratings.title_id', '=', 'titles.id')
            ->select(
                'titles.id',
                'titles.title',
                'titles.type',
                'titles.release_year',
                'titles.synopsis',
                DB::raw('AVG(ratings.score) as avg_score')
            )
            ->groupBy('titles.id', 'titles.title', 'titles.type', 'titles.release_year', 'titles.synopsis');

        if ($type !== 'semua') {
            $query->where('titles.type', $type);
        }

        $titles = $query->get();

        // Genre tiap judul
        $genreMap = DB::table('genre_title')
            ->join('genres', 'genres.id', '=', 'genre_title.genre_id')
            ->whereIn('genre_title.title_id', $titles->pluck('id')->all())
            ->select('genre_title.title_id', 'genres.name')
            ->get()
            ->groupBy('title_id');

        // Genre favorit user yang sedang login
        $favorites = DB::table('user_favorite_genres')
            ->join('genres', 'genres.id', '=', 'user_favorite_genres.genre_id')
            ->where('user_favorite_genres.user_id', Auth::id())
            ->pluck('genres.name')
            ->all();

        foreach ($titles as $t) {
            $t->genres = $genreMap->has($t->id) ? $genreMap[$t->id]->pluck('name')->all() : [];
            $t->avg_score = $t->avg_score !== null ? (float) $t->avg_score : null;

            // Persen kecocokan = genre judul yang ada di genre favorit user
            $t->match = (count($favorites) > 0 && count($t->genres) > 0)
                ? (int) round(count(array_intersect($t->genres, $favorites)) / count($t->genres) * 100)
                : null;
        }

        // Urutkan: paling cocok dulu, lalu rating tertinggi
        return $titles->sort(function ($a, $b) {
            return [$b->match ?? 0, $b->avg_score ?? 0] <=> [$a->match ?? 0, $a->avg_score ?? 0];
        })->values();
    }
}