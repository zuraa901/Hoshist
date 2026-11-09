<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(
            [
                'body' => ['required', 'string', 'max:500'],
            ],
            [
                'body.required' => 'Tulis sesuatu dulu sebelum memposting.',
                'body.max'      => 'Postingan maksimal 500 karakter.',
            ]
        );

        $now = now();

        DB::table('posts')->insert([
            'user_id'    => Auth::id(),
            'title_id'   => null,
            'body'       => trim($data['body']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return redirect()->route('home')->with('status', 'Postingan terkirim.');
    }
}