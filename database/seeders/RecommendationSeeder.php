<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecommendationSeeder extends Seeder
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

        $data = [
            ['Langit Terakhir', 'anime', 2024, 'Seorang penjaga mercusuar menemukan bahwa setiap bintang yang jatuh membawa satu kenangan.', ['Fantasy', 'Drama'], 9],
            ['Surat untuk Hoshi', 'manga', 2022, 'Surat-surat yang tak pernah terkirim mempertemukan dua orang asing.', ['Fantasy', 'Slice of Life'], 9],
            ['Musim Kedua Kita', 'drama', 2023, 'Dua mantan kekasih bertemu lagi di kota kecil tempat semuanya bermula.', ['Drama', 'Romance'], 8],
            ['Kota Senja', 'film', 2021, 'Seorang fotografer menyusuri kota yang selalu berada dalam waktu senja.', ['Drama', 'Fantasy'], 8],
            ['Pelukis Angin', 'manga', 2020, 'Seorang pelukis muda belajar melukis hal-hal yang tidak terlihat.', ['Fantasy', 'Action'], 9],
            ['Dapur Tengah Malam', 'drama', 2022, 'Warung kecil yang hanya buka tengah malam dan para pelanggan setianya.', ['Slice of Life', 'Comedy'], 8],
            ['Kapal Kertas', 'anime', 2023, 'Sekelompok anak melipat kapal kertas yang membawa harapan lewat sungai.', ['Slice of Life', 'Fantasy'], 9],
            ['Samurai Kopi', 'komik', 2023, 'Seorang samurai pensiun dan membuka kedai kopi di desa terpencil.', ['Comedy', 'Action'], 7],
        ];

        $userIds = DB::table('users')->pluck('id');

        foreach ($data as [$title, $type, $year, $synopsis, $genres, $score]) {
            DB::table('titles')->updateOrInsert(
                ['title' => $title],
                ['type' => $type, 'release_year' => $year, 'synopsis' => $synopsis, 'created_at' => $now, 'updated_at' => $now]
            );
            $titleId = DB::table('titles')->where('title', $title)->value('id');

            
            foreach ($genres as $genreName) {
                $genreId = DB::table('genres')->where('name', $genreName)->value('id');
                DB::table('genre_title')->updateOrInsert(
                    ['title_id' => $titleId, 'genre_id' => $genreId]
                );
            }

            
            foreach ($userIds as $userId) {
                DB::table('ratings')->updateOrInsert(
                    ['user_id' => $userId, 'title_id' => $titleId],
                    ['score' => $score, 'created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }
}