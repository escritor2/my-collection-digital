<?php

namespace App\Services\Modules\Catalog;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ExternalCatalogService
{
    public function search(string $query, int $limit = 8): array
    {
        $query = trim($query);
        if ($query === '') {
            return ['data' => []];
        }

        $cacheKey = 'catalog:search:'.sha1(mb_strtolower($query).':'.$limit);

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($query, $limit) {
            $googleParams = [
                'q' => $query,
                'maxResults' => min($limit, 40),
                'printType' => 'books',
            ];
            if ($key = config('services.google_books.key')) {
                $googleParams['key'] = $key;
            }

            $responses = Http::pool(fn ($pool) => [
                $pool->as('google')
                    ->timeout(6)
                    ->withoutVerifying()
                    ->withHeaders(['Accept' => 'application/json'])
                    ->get('https://www.googleapis.com/books/v1/volumes', $googleParams),
                $pool->as('ol')
                    ->timeout(6)
                    ->withoutVerifying()
                    ->withHeaders(['Accept' => 'application/json'])
                    ->get('https://openlibrary.org/search.json', ['q' => $query, 'limit' => min($limit, 50)]),
                $pool->as('itunes')
                    ->timeout(6)
                    ->withoutVerifying()
                    ->get('https://itunes.apple.com/search', [
                        'term' => $query,
                        'entity' => 'ebook',
                        'limit' => $limit,
                        'country' => 'br'
                    ]),
            ]);

            $google = [];
            $ol = [];
            $itunes = [];

            try {
                $googleResponse = $responses['google'] ?? null;
                if ($googleResponse instanceof \Illuminate\Http\Client\Response && $googleResponse->ok()) {
                    $google = $this->parseGoogleBooksResponse($googleResponse->json('items') ?? []);
                } else {
                    $err = $googleResponse instanceof \Exception ? $googleResponse->getMessage() : 'Status: ' . ($googleResponse?->status() ?? 'unknown');
                    Log::warning('Google Books Search failed: ' . $err);
                }
            } catch (\Exception $e) {
                Log::error('Google Books Search parse failed: '.$e->getMessage());
            }

            try {
                $olResponse = $responses['ol'] ?? null;
                if ($olResponse instanceof \Illuminate\Http\Client\Response && $olResponse->ok()) {
                    $ol = $this->parseOpenLibraryResponse($olResponse->json('docs') ?? []);
                } else {
                    $err = $olResponse instanceof \Exception ? $olResponse->getMessage() : 'Status: ' . ($olResponse?->status() ?? 'unknown');
                    Log::warning('Open Library Search failed: ' . $err);
                }
            } catch (\Exception $e) {
                Log::error('Open Library Search parse failed: '.$e->getMessage());
            }

            try {
                $itunesResponse = $responses['itunes'] ?? null;
                if ($itunesResponse instanceof \Illuminate\Http\Client\Response && $itunesResponse->ok()) {
                    $itunes = $this->parseItunesResponse($itunesResponse->json('results') ?? []);
                }
            } catch (\Exception $e) {
                Log::error('iTunes Search parse failed: '.$e->getMessage());
            }

            // Simple merge + de-dup by isbn/title+author
            $items = [];
            $seen = [];
            foreach (array_merge($google, $ol, $itunes) as $item) {
                $k = $item['isbn'] ? ('isbn:'.$item['isbn']) : ('ta:'.sha1(mb_strtolower(($item['title'] ?? '').'|'.($item['author'] ?? ''))));
                if (isset($seen[$k])) {
                    continue;
                }
                $seen[$k] = true;
                $items[] = $item;
                if (count($items) >= $limit) {
                    break;
                }
            }

            return ['data' => $items];
        });
    }

    public function resolveByIsbn(string $isbn): ?array
    {
        $isbn = preg_replace('/[^0-9Xx]/', '', $isbn ?? '');
        if ($isbn === '') {
            return null;
        }

        $cacheKey = 'catalog:resolve:isbn:'.sha1($isbn);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($isbn) {
            try {
                $google = $this->resolveGoogleByIsbn($isbn);
                if ($google) {
                    return $google;
                }
            } catch (\Exception $e) {
                Log::error('Google Books Resolve failed: '.$e->getMessage());
            }

            try {
                return $this->resolveOpenLibraryByIsbn($isbn);
            } catch (\Exception $e) {
                Log::error('Open Library Resolve failed: '.$e->getMessage());

                return null;
            }
        });
    }

    protected function getHttpClient()
    {
        return Http::timeout(10)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'application/json',
            ])
            ->when(app()->environment('local'), fn ($h) => $h->withoutVerifying());
    }

    protected function parseGoogleBooksResponse(array $items): array
    {
        $out = [];
        foreach ($items as $it) {
                $v = $it['volumeInfo'] ?? [];
                $a = $it['accessInfo'] ?? [];
                $industry = $v['industryIdentifiers'] ?? [];
                $isbn = $this->pickIsbn($industry);

                $out[] = $this->normalize([
                    'source' => 'google',
                    'external_id' => $it['id'] ?? null,
                    'title' => $v['title'] ?? null,
                    'author' => isset($v['authors']) ? implode(', ', (array) $v['authors']) : null,
                    'description' => $v['description'] ?? null,
                    'isbn' => $isbn,
                    'page_count' => $v['pageCount'] ?? null,
                    'cover_url' => $v['imageLinks']['thumbnail'] ?? $v['imageLinks']['smallThumbnail'] ?? null,
                    'language' => $v['language'] ?? null,
                    'publisher' => $v['publisher'] ?? null,
                    'published_date' => $v['publishedDate'] ?? null,
                    'categories' => $v['categories'] ?? null,
                    'preview_link' => $v['previewLink'] ?? null,
                    'web_reader_link' => $a['webReaderLink'] ?? null,
                    'pdf_link' => ($a['pdf']['isAvailable'] ?? false) ? ($a['pdf']['downloadLink'] ?? $a['pdf']['acsTokenLink'] ?? null) : null,
                    'epub_link' => ($a['epub']['isAvailable'] ?? false) ? ($a['epub']['downloadLink'] ?? $a['epub']['acsTokenLink'] ?? null) : null,
                ]);
        }

        return $out;
    }

    protected function resolveGoogleByIsbn(string $isbn): ?array
    {
        $key = config('services.google_books.key');
        $params = [
            'q' => 'isbn:'.$isbn,
            'maxResults' => 1,
            'printType' => 'books',
        ];
        if ($key) {
            $params['key'] = $key;
        }

        $res = $this->getHttpClient()->get('https://www.googleapis.com/books/v1/volumes', $params);
        if (! $res->ok()) {
            return null;
        }
        $items = $res->json('items') ?? [];
        if (count($items) === 0) {
            return null;
        }

        $it = $items[0];
        $v = $it['volumeInfo'] ?? [];
        $industry = $v['industryIdentifiers'] ?? [];
        $picked = $this->pickIsbn($industry) ?: $isbn;

        return $this->normalize([
            'source' => 'google',
            'external_id' => $it['id'] ?? null,
            'title' => $v['title'] ?? null,
            'author' => isset($v['authors']) ? implode(', ', (array) $v['authors']) : null,
            'description' => $v['description'] ?? null,
            'isbn' => $picked,
            'page_count' => $v['pageCount'] ?? null,
            'cover_url' => $v['imageLinks']['thumbnail'] ?? $v['imageLinks']['smallThumbnail'] ?? null,
            'language' => $v['language'] ?? null,
            'publisher' => $v['publisher'] ?? null,
            'published_date' => $v['publishedDate'] ?? null,
            'categories' => $v['categories'] ?? null,
        ]);
    }

    protected function parseOpenLibraryResponse(array $docs): array
    {
        $out = [];
        foreach ($docs as $d) {
            $isbn = isset($d['isbn']) ? ($d['isbn'][0] ?? null) : null;
            $olKey = $d['key'] ?? null;

            $cover = null;
            if (! empty($d['cover_i'])) {
                $cover = 'https://covers.openlibrary.org/b/id/'.$d['cover_i'].'-M.jpg';
            }

            $out[] = $this->normalize([
                'source' => 'openlibrary',
                'external_id' => $olKey,
                'title' => $d['title'] ?? null,
                'author' => isset($d['author_name']) ? implode(', ', (array) $d['author_name']) : null,
                'description' => null,
                'isbn' => $isbn,
                'page_count' => $d['number_of_pages_median'] ?? null,
                'cover_url' => $cover,
                'language' => isset($d['language']) ? ($d['language'][0] ?? null) : null,
                'publisher' => isset($d['publisher']) ? ($d['publisher'][0] ?? null) : null,
                'published_date' => isset($d['first_publish_year']) ? (string) $d['first_publish_year'] : null,
                'categories' => isset($d['subject']) ? array_slice((array) $d['subject'], 0, 8) : null,
            ]);
        }

        return $out;
    }

    protected function resolveOpenLibraryByIsbn(string $isbn): ?array
    {
        // Open Library Books API
        $res = $this->getHttpClient()->get('https://openlibrary.org/api/books', [
            'bibkeys' => 'ISBN:'.$isbn,
            'format' => 'json',
            'jscmd' => 'data',
        ]);
        if (! $res->ok()) {
            return null;
        }

        $data = $res->json();
        $key = 'ISBN:'.$isbn;
        $b = $data[$key] ?? null;
        if (! $b) {
            return null;
        }

        $authors = isset($b['authors']) ? implode(', ', array_map(fn ($a) => $a['name'] ?? '', (array) $b['authors'])) : null;
        $cover = $b['cover']['medium'] ?? $b['cover']['small'] ?? null;

        return $this->normalize([
            'source' => 'openlibrary',
            'external_id' => $b['key'] ?? null,
            'title' => $b['title'] ?? null,
            'author' => $authors,
            'description' => is_array($b['description'] ?? null) ? ($b['description']['value'] ?? null) : ($b['description'] ?? null),
            'isbn' => $isbn,
            'page_count' => $b['number_of_pages'] ?? null,
            'cover_url' => $cover,
            'language' => null,
            'publisher' => isset($b['publishers']) ? ($b['publishers'][0]['name'] ?? null) : null,
            'published_date' => $b['publish_date'] ?? null,
            'categories' => isset($b['subjects']) ? array_slice(array_map(fn ($s) => $s['name'] ?? null, (array) $b['subjects']), 0, 8) : null,
        ]);
    }

    protected function parseItunesResponse(array $results): array
    {
        $out = [];
        foreach ($results as $r) {
            $cover = $r['artworkUrl100'] ?? null;
            if ($cover) {
                // Try to get higher resolution
                $cover = str_replace('100x100bb.jpg', '600x600bb.jpg', $cover);
            }

            $out[] = $this->normalize([
                'source' => 'itunes',
                'external_id' => (string) ($r['trackId'] ?? null),
                'title' => $r['trackName'] ?? null,
                'author' => $r['artistName'] ?? null,
                'description' => strip_tags($r['description'] ?? ''),
                'isbn' => null, // iTunes doesn't return ISBN directly
                'page_count' => null,
                'cover_url' => $cover,
                'language' => null,
                'publisher' => null,
                'published_date' => isset($r['releaseDate']) ? substr($r['releaseDate'], 0, 4) : null,
                'categories' => isset($r['genres']) ? (array) $r['genres'] : null,
            ]);
        }

        return $out;
    }

    protected function pickIsbn(array $industryIdentifiers): ?string
    {
        $isbn13 = null;
        $isbn10 = null;
        foreach ($industryIdentifiers as $id) {
            if (($id['type'] ?? '') === 'ISBN_13') {
                $isbn13 = $id['identifier'] ?? null;
            }
            if (($id['type'] ?? '') === 'ISBN_10') {
                $isbn10 = $id['identifier'] ?? null;
            }
        }

        return $isbn13 ?: $isbn10;
    }

    protected function normalize(array $raw): array
    {
        $title = $raw['title'] ?? null;
        $author = $raw['author'] ?? null;

        return [
            'source' => $raw['source'] ?? null,
            'external_id' => $raw['external_id'] ?? null,
            'title' => $title ? Str::of($title)->trim()->toString() : null,
            'author' => $author ? Str::of($author)->trim()->toString() : null,
            'description' => $raw['description'] ?? null,
            'isbn' => $raw['isbn'] ?? null,
            'page_count' => $raw['page_count'] ?? null,
            'cover_url' => $raw['cover_url'] ?? null,
            'language' => $raw['language'] ?? null,
            'publisher' => $raw['publisher'] ?? null,
            'published_date' => $raw['published_date'] ?? null,
            'categories' => $raw['categories'] ?? null,
            'preview_link' => $raw['preview_link'] ?? null,
            'web_reader_link' => $raw['web_reader_link'] ?? null,
            'pdf_link' => $raw['pdf_link'] ?? null,
            'epub_link' => $raw['epub_link'] ?? null,
        ];
    }
}
