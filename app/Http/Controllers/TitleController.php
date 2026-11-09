<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TitleController extends Controller
{
    // Halaman detail judul
    public function show(int $id): View
    {
        $title = DB::table('titles')->where('id', $id)->first();
        abort_if($title === null, 404);

        
        $stats = DB::table('ratings')
            ->where('title_id', $id)
            ->selectRaw('AVG(score) as avg_score, COUNT(*) as total')
            ->first();


        $genres = DB::table('genre_title')
            ->join('genres', 'genres.id', '=', 'genre_title.genre_id')
            ->where('genre_title.title_id', $id)
            ->orderBy('genres.name')
            ->pluck('genres.name');

        $myRating = DB::table('ratings')
            ->where('title_id', $id)
            ->where('user_id', Auth::id())
            ->first();

        $reviews = DB::table('ratings')
            ->join('users', 'users.id', '=', 'ratings.user_id')
            ->where('ratings.title_id', $id)
            ->whereNotNull('ratings.review')
            ->where('ratings.review', '!=', '')
            ->orderByDesc('ratings.updated_at')
            ->select('users.name', 'users.username', 'ratings.score', 'ratings.review', 'ratings.updated_at')
            ->limit(30)
            ->get();

        return view('detail', compact('title', 'stats', 'genres', 'myRating', 'reviews'));
    }

    
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            return response()->json([]);
        }

        $titles = DB::table('titles')
            ->where('title', 'like', '%' . $q . '%')
            ->orderByRaw('CASE WHEN title LIKE ? THEN 0 ELSE 1 END', [$q . '%'])
            ->orderBy('title')
            ->limit(8)
            ->select('id', 'title', 'type', 'release_year')
            ->get();

        return response()->json($titles);
    }
}