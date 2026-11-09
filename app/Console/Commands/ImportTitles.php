<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ImportTitles extends Command
{
    protected $signature = 'hoshist:import {--pages=2 : Jumlah halaman Jikan per kategori}';

    protected $description = 'Mengambil data judul asli: film (iTunes), drama (TVmaze), anime/manga/komik (Jikan). Semua tanpa API key.';

    private int $saved = 0;

    public function handle(): int
    {
        $pages = max(1, (int) $this->option('pages'));

        $this->importFilms();
        $this->importDramas();
        $this->importJikan($pages);

        $this->info("Selesai. {$this->saved} judul disimpan atau diperbarui.");

        return self::SUCCESS;
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()
            ->timeout(30)
            ->withOptions(['verify' => (bool) config('hoshist.ssl_verify', true)]);
    }

    // ----- Film (iTunes Search API) -----
    private function importFilms(): void
    {
        $this->info('Mengambil film dari iTunes...');
        $http = $this->client();

        foreach (['action', 'comedy', 'drama', 'romance', 'fantasy', 'thriller', 'animation', 'adventure'] as $term) {
            $res = $http->get('https://itunes.apple.com/search', [
                'term'    => $term,
                'media'   => 'movie',
                'entity'  => 'movie',
                'limit'   => 25,
                'country' => 'US',
            ]);

            if (! $res->successful()) {
                $this->warn("  Film '{$term}' gagal (HTTP {$res->status()}).");
                sleep(5);
                continue;
            }

            foreach ($res->json('results', []) as $m) {
                if (($m['kind'] ?? '') !== 'feature-movie' || empty($m['trackId'])) {
                    continue;
                }

                $poster = $m['artworkUrl100'] ?? null;
                if ($poster) {
                    $poster = str_replace('100x100bb', '300x300bb', $poster);
                }

                $this->saveTitle(
                    'itunes-movie',
                    (string) $m['trackId'],
                    (string) ($m['trackName'] ?? ''),
                    'film',
                    $this->year($m['releaseDate'] ?? null),
                    $m['longDescription'] ?? ($m['shortDescription'] ?? null),
                    $poster,
                    $this->splitGenres((string) ($m['primaryGenreName'] ?? ''))
                );
            }

            sleep(2);
        }
    }

    // ----- Drama (TVmaze) -----
    private function importDramas(): void
    {
        $this->info('Mengambil drama dari TVmaze...');
        $http = $this->client();

        $shows = [];
        foreach ([0, 1] as $page) {
            $res = $http->get('https://api.tvmaze.com/shows', ['page' => $page]);

            if (! $res->successful()) {
                $this->warn("  Drama halaman {$page} gagal (HTTP {$res->status()}).");
                continue;
            }

            foreach ($res->json() ?? [] as $s) {
                $genres = $s['genres'] ?? [];
                if (in_array('Drama', $genres, true) && ! empty(data_get($s, 'image.medium'))) {
                    $shows[] = $s;
                }
            }

            sleep(1);
        }

        // Ambil 60 serial drama paling populer
        usort($shows, fn ($a, $b) => ($b['weight'] ?? 0) <=> ($a['weight'] ?? 0));

        foreach (array_slice($shows, 0, 60) as $s) {
            $summary = isset($s['summary']) ? trim(strip_tags((string) $s['summary'])) : null;
            $poster  = str_replace('http://', 'https://', (string) data_get($s, 'image.medium'));

            $this->saveTitle(
                'tvmaze',
                (string) $s['id'],
                (string) ($s['name'] ?? ''),
                'drama',
                $this->year($s['premiered'] ?? null),
                $summary,
                $poster,
                $s['genres'] ?? []
            );
        }
    }

    // ----- Anime, manga, komik (Jikan / MyAnimeList) -----
    private function importJikan(int $pages): void
    {
        $http = $this->client();

        $jobs = [
            ['label' => 'anime',          'url' => 'https://api.jikan.moe/v4/top/anime', 'params' => [],                  'source' => 'jikan-anime', 'type' => 'anime'],
            ['label' => 'manga',          'url' => 'https://api.jikan.moe/v4/top/manga', 'params' => ['type' => 'manga'],  'source' => 'jikan-manga', 'type' => 'manga'],
            ['label' => 'komik (manhwa)', 'url' => 'https://api.jikan.moe/v4/top/manga', 'params' => ['type' => 'manhwa'], 'source' => 'jikan-manga', 'type' => 'komik'],
        ];

        foreach ($jobs as $job) {
            $this->info("Mengambil {$job['label']} dari Jikan (MyAnimeList)...");

            for ($page = 1; $page <= $pages; $page++) {
                $params = $job['params'] + ['page' => $page, 'limit' => 25, 'sfw' => 'true'];

                $res = $http->get($job['url'], $params);

                // Kena batas permintaan: tunggu sebentar lalu coba sekali lagi
                if ($res->status() === 429) {
                    sleep(5);
                    $res = $http->get($job['url'], $params);
                }

                if (! $res->successful()) {
                    $this->warn("  {$job['label']} halaman {$page} gagal (HTTP {$res->status()}).");
                    continue;
                }

                foreach ($res->json('data', []) as $item) {
                    $this->saveTitle(
                        $job['source'],
                        (string) $item['mal_id'],
                        (string) ($item['title'] ?? ''),
                        $job['type'],
                        $this->year(
                            data_get($item, 'year')
                                ?? data_get($item, 'aired.prop.from.year')
                                ?? data_get($item, 'published.prop.from.year')
                        ),
                        $item['synopsis'] ?? null,
                        data_get($item, 'images.jpg.image_url'),
                        array_column($item['genres'] ?? [], 'name')
                    );
                }

                usleep(1100000); // jaga batas 3 permintaan/detik dan 60 permintaan/menit
            }
        }
    }

    // ----- Pembantu -----
    private function splitGenres(string $name): array
    {
        return array_values(array_filter(array_map('trim', explode('&', $name))));
    }

    private function year(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $year = (int) substr((string) $value, 0, 4);

        return ($year >= 1901 && $year <= 2155) ? $year : null;
    }

    private function saveTitle(
        string $source,
        string $externalId,
        string $title,
        string $type,
        ?int $year,
        ?string $synopsis,
        ?string $poster,
        array $genreNames
    ): void {
        $title = trim($title);
        if ($title === '') {
            return;
        }

        $now = now();

        $values = [
            'title'        => mb_substr($title, 0, 255),
            'type'         => $type,
            'release_year' => $year,
            'synopsis'     => ($synopsis !== null && trim($synopsis) !== '') ? trim($synopsis) : null,
            'poster'       => $poster,
            'updated_at'   => $now,
        ];

        $existing = DB::table('titles')
            ->where('source', $source)
            ->where('external_id', $externalId)
            ->first();

        if ($existing) {
            DB::table('titles')->where('id', $existing->id)->update($values);
            $titleId = $existing->id;
        } else {
            $titleId = DB::table('titles')->insertGetId(
                $values + ['source' => $source, 'external_id' => $externalId, 'created_at' => $now]
            );
        }

        foreach (array_unique($genreNames) as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }

            DB::table('genres')->updateOrInsert(
                ['name' => $name],
                ['created_at' => $now, 'updated_at' => $now]
            );
            $genreId = DB::table('genres')->where('name', $name)->value('id');

            DB::table('genre_title')->updateOrInsert(
                ['title_id' => $titleId, 'genre_id' => $genreId]
            );
        }

        $this->saved++;
    }
}