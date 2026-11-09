<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DetailController extends Controller
{
    public function show($id)
    {
    

        $film = [
            'id' => $id,
            'judul' => 'Langit Terakhir',
            'kategori' => 'Anime',
            'tahun' => 2024,
            'rating' => 8.9,
            'jumlah_rating' => 1240,

            'poster' => null,

            'genre' => [
                'Fantasy',
                'Drama'
            ],

            'sinopsis' => 'Seorang penjaga mercusuar menemukan bahwa setiap bintang yang jatuh membawa satu kenangan dari orang yang telah hilang.'
        ];


   
        $reviews = [

            [
                'initial' => 'D',
                'nama' => 'Dimas',
                'username' => '@dimas.r',
                'waktu' => '2j',
                'score' => 9,
                'review' => 'Visualnya tenang, ceritanya pelan tapi nancep.',
                'komentar' => 24,
                'like' => 138
            ],

            [
                'initial' => 'A',
                'nama' => 'Alya',
                'username' => '@alyua',
                'waktu' => '1h',
                'score' => 8,
                'review' => 'Awalnya agak lambat, tapi akhirnya terbayar. Soundtrack-nya juara.',
                'komentar' => 11,
                'like' => 52
            ]

        ];



        $trending = [

            [
                'kategori' => 'Anime',
                'judul' => 'Langit Terakhir',
                'jumlah_rating' => '1,2 rb'
            ],

            [
                'kategori' => 'Manga',
                'judul' => 'Surat untuk Hoshi',
                'jumlah_rating' => '860'
            ],

            [
                'kategori' => 'Drama',
                'judul' => 'Musim Kedua Kita',
                'jumlah_rating' => '640'
            ],

            [
                'kategori' => 'Film',
                'judul' => 'Kota Senja',
                'jumlah_rating' => '512'
            ]

        ];


      

        $friends = [

            [
                'initial' => 'R',
                'nama' => 'Raka',
                'genre_sama' => '3 genre sama'
            ],

            [
                'initial' => 'S',
                'nama' => 'Sinta',
                'genre_sama' => '2 genre sama'
            ],

            [
                'initial' => 'B',
                'nama' => 'Bayu',
                'genre_sama' => '2 genre sama'
            ]

        ];


        return view('detail', compact(
            'film',
            'reviews',
            'trending',
            'friends'
        ));
    }
}
