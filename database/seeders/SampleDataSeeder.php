<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

    
        $genreNames = ['Fantasy', 'Drama', 'Romance', 'Slice of Life', 'Action', 'Comedy'];
        foreach ($genreNames as $name) {
            DB::table('genres')->updateOrInsert(
                ['name' => $name],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }

        $titles = [
            ['Langit Terakhir', 'anime', 2024, 'Seorang penjaga mercusuar menemukan bahwa setiap bintang yang jatuh membawa satu kenangan.'],
            ['Surat untuk Hoshi', 'manga', 2022, 'Kumpulan surat yang tak pernah terkirim mempertemukan dua orang asing.'],
            ['Musim Kedua Kita', 'drama', 2023, 'Dua mantan kekasih bertemu lagi di kota kecil tempat semuanya bermula.'],
            ['Kota Senja', 'film', 2021, 'Seorang fotografer menyusuri kota yang selalu berada dalam waktu senja.'],
        ];
        foreach ($titles as [$title, $type, $year, $synopsis]) {
            DB::table('titles')->updateOrInsert(
                ['title' => $title],
                ['type' => $type, 'release_year' => $year, 'synopsis' => $synopsis, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        
        $ratings = [
            ['Surat untuk Hoshi', 9, 'Pelan, hangat, dan bikin nangis di bab terakhir.'],
            ['Langit Terakhir', 9, 'Visualnya tenang, ceritanya pelan tapi nancep.'],
            ['Musim Kedua Kita', 8, 'Chemistry pemerannya enak diikuti sampai akhir.'],
        ];
        $favoriteGenres = ['Fantasy', 'Slice of Life', 'Drama'];

        foreach (DB::table('users')->get() as $user) {
            foreach ($ratings as [$title, $score, $review]) {
                $titleId = DB::table('titles')->where('title', $title)->value('id');
                DB::table('ratings')->updateOrInsert(
                    ['user_id' => $user->id, 'title_id' => $titleId],
                    ['score' => $score, 'review' => $review, 'created_at' => $now, 'updated_at' => $now]
                );
            }

            foreach ($favoriteGenres as $genreName) {
                $genreId = DB::table('genres')->where('name', $genreName)->value('id');
                DB::table('user_favorite_genres')->updateOrInsert(
                    ['user_id' => $user->id, 'genre_id' => $genreId]
                );
            }
        }
    }
}