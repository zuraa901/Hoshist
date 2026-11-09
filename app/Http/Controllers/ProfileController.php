<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = Auth::user();

       
        $stats = [
            'ratings'   => DB::table('ratings')->where('user_id', $user->id)->count(),
            'following' => DB::table('follows')->where('follower_id', $user->id)->count(),
            'followers' => DB::table('follows')->where('following_id', $user->id)->count(),
        ];

       
        $genres = DB::table('user_favorite_genres')
            ->join('genres', 'genres.id', '=', 'user_favorite_genres.genre_id')
            ->where('user_favorite_genres.user_id', $user->id)
            ->orderBy('genres.name')
            ->pluck('genres.name');

        
        $ratings = DB::table('ratings')
            ->join('titles', 'titles.id', '=', 'ratings.title_id')
            ->where('ratings.user_id', $user->id)
            ->orderByDesc('ratings.created_at')
            ->select('titles.title', 'titles.type', 'ratings.score', 'ratings.review', 'ratings.created_at')
            ->get();

        return view('profil', compact('user', 'stats', 'genres', 'ratings'));

        
    }

    

public function edit()
{
    $user = Auth::user();

    
    $availableGenres = DB::table('genres')
        ->orderBy('name')
        ->get();

    
    $selectedGenreIds = DB::table('user_favorite_genres')
        ->where('user_id', $user->id)
        ->pluck('genre_id')
        ->toArray();

    return view('edit-profil', compact(
        'user',
        'availableGenres',
        'selectedGenreIds'
    ));
}




public function update(Request $request)
{
    $user = auth()->user();

    $validated = $request->validate([
        'name' => ['required', 'string', 'max:100'],

        'username' => [
            'required',
            'string',
            'max:30',
            'alpha_dash',
            Rule::unique('users', 'username')->ignore($user->id),
        ],

        'bio' => ['nullable', 'string', 'max:250'],

        'avatar' => [
            'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:2048',
        ],

        'banner' => [
            'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:5120',
        ],

        'genre_ids' => ['nullable', 'array'],

        'genre_ids.*' => [
            'integer',
            'distinct',
            'exists:genres,id'
        ],
    ]);

    
    $user->name = $validated['name'];
    $user->username = $validated['username'];
    $user->bio = $validated['bio'] ?? null;


    
    if ($request->hasFile('avatar')) {

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->avatar_path = $request->file('avatar')
            ->store('avatars', 'public');
    }

    
    elseif ($request->boolean('remove_avatar')) {

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->avatar_path = null;
    }


    
    if ($request->hasFile('banner')) {

        if ($user->banner_path) {
            Storage::disk('public')->delete($user->banner_path);
        }

        $user->banner_path = $request->file('banner')
            ->store('banners', 'public');
    }

   
    elseif ($request->boolean('remove_banner')) {

        if ($user->banner_path) {
            Storage::disk('public')->delete($user->banner_path);
        }

        $user->banner_path = null;
    }


    
    $user->save();

    
    

    $genreIds = $validated['genre_ids'] ?? [];

    
    DB::table('user_favorite_genres')
        ->where('user_id', $user->id)
        ->delete();

    
    if (count($genreIds) > 0) {

        $genreData = [];

        foreach ($genreIds as $genreId) {
            $genreData[] = [
                'user_id' => $user->id,
                'genre_id' => $genreId,
            ];
        }

        DB::table('user_favorite_genres')->insert($genreData);
    }


    return redirect()
        ->route('profile')
        ->with('success', 'Profil berhasil diperbarui!');

        
}

}