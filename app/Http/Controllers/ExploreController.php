<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExploreController extends Controller
{
    public function index(Request $request): View
    {
        $q    = trim((string) $request->query('q', ''));
        $tab  = $request->query('tab');
        $sort = $request->query('sort');
        $type = $request->query('type');

        // Awali dengan @ untuk langsung mencari pengguna
        if ($tab === null && str_starts_with($q, '@')) {
            $tab = 'pengguna';
        }
        if (! in_array($tab, ['judul', 'pengguna'], true)) {
            $tab = 'judul';
        }
        if (! in_array($sort, ['rating', 'terbaru', 'populer'], true)) {
            $sort = 'rating';
        }
        if (! in_array($type, ['anime', 'film', 'drama', 'manga', 'komik'], true)) {
            $type = 'semua';
        }

        $genres = DB::table('genres')->orderBy('name')->pluck('name')->all();
        $genre  = (string) $request->query('genre', '');
        if (! in_array($genre, $genres, true)) {
            $genre = '';
        }

        // Pencarian dijalankan kalau ada kata kunci atau filter yang dipilih
        $searched = $q !== '' || $genre !== '' || ($tab === 'judul' && $type !== 'semua');

        $results = collect();
        if ($searched) {
            $results = $tab === 'pengguna'
                ? $this->searchUsers($q, $genre)
                : $this->searchTitles($q, $type, $genre, $sort);
        }

        return view('jelajahi', compact('q', 'tab', 'type', 'genre', 'sort', 'genres', 'searched', 'results'));
    }

    private function searchTitles(string $q, string $type, string $genre, string $sort): Collection
    {
        $query = DB::table('titles')
            ->leftJoin('ratings', 'ratings.title_id', '=', 'titles.id')
            ->select(
                'titles.id',
                'titles.title',
                'titles.type',
                'titles.release_year',
                DB::raw('AVG(ratings.score) as avg_score'),
                DB::raw('COUNT(ratings.id) as rating_count')
            )
            ->groupBy('titles.id', 'titles.title', 'titles.type', 'titles.release_year');

        if ($q !== '') {
            $query->where('titles.title', 'like', '%' . $q . '%');
        }

        if ($type !== 'semua') {
            $query->where('titles.type', $type);
        }

        if ($genre !== '') {
            $query->whereIn('titles.id', function ($sub) use ($genre) {
                $sub->select('genre_title.title_id')
                    ->from('genre_title')
                    ->join('genres', 'genres.id', '=', 'genre_title.genre_id')
                    ->where('genres.name', $genre);
            });
        }

        if ($sort === 'terbaru') {
            $query->orderByDesc('titles.release_year');
        } elseif ($sort === 'populer') {
            $query->orderByDesc('rating_count');
        } else {
            $query->orderByDesc('avg_score');
        }
        $query->orderBy('titles.title');

        $titles = $query->limit(30)->get();

        $genreMap = DB::table('genre_title')
            ->join('genres', 'genres.id', '=', 'genre_title.genre_id')
            ->whereIn('genre_title.title_id', $titles->pluck('id')->all())
            ->select('genre_title.title_id', 'genres.name')
            ->get()
            ->groupBy('title_id');

        foreach ($titles as $t) {
            $t->genres = $genreMap->has($t->id) ? $genreMap[$t->id]->pluck('name')->all() : [];
            $t->avg_score = $t->avg_score !== null ? (float) $t->avg_score : null;
        }

        return $titles;
    }

    private function searchUsers(string $q, string $genre): Collection
    {
        $term = ltrim($q, '@');

        $query = DB::table('users')->where('users.id', '!=', Auth::id());

        if ($term !== '') {
            $query->where(function ($w) use ($term) {
                $w->where('users.name', 'like', '%' . $term . '%')
                  ->orWhere('users.username', 'like', '%' . $term . '%');
            });
        }

        if ($genre !== '') {
            $query->whereIn('users.id', function ($sub) use ($genre) {
                $sub->select('user_favorite_genres.user_id')
                    ->from('user_favorite_genres')
                    ->join('genres', 'genres.id', '=', 'user_favorite_genres.genre_id')
                    ->where('genres.name', $genre);
            });
        }

        $users = $query
            ->select('users.id', 'users.name', 'users.username')
            ->orderBy('users.username')
            ->limit(30)
            ->get();

        $genreMap = DB::table('user_favorite_genres')
            ->join('genres', 'genres.id', '=', 'user_favorite_genres.genre_id')
            ->whereIn('user_favorite_genres.user_id', $users->pluck('id')->all())
            ->select('user_favorite_genres.user_id', 'genres.name')
            ->get()
            ->groupBy('user_id');

        $mine = DB::table('user_favorite_genres')
            ->join('genres', 'genres.id', '=', 'user_favorite_genres.genre_id')
            ->where('user_favorite_genres.user_id', Auth::id())
            ->pluck('genres.name')
            ->all();

        foreach ($users as $u) {
            $u->genres = $genreMap->has($u->id) ? $genreMap[$u->id]->pluck('name')->all() : [];
            $u->shared = count(array_intersect($u->genres, $mine));
        }

        return $users;
    }
}