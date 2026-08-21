<?php

namespace App\Services;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\Season;
use App\Models\TvShow;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TmdbImportService
{
    public function __construct(
        private TmdbClient $client,
    ) {}

    public function importMovie(int $tmdbId, ?string $language = null): Movie
    {
        $params = $language ? ['language' => $language] : [];
        $data = $this->client->getMovie($tmdbId, $params);

        $baseSlug = Str::slug($data['title']);
        $slug = $baseSlug;
        $counter = 1;
        while (Movie::where('slug', $slug)->where('tmdb_id', '!=', $tmdbId)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $trailerUrl = null;
        if (!empty($data['videos']['results'])) {
            $trailer = collect($data['videos']['results'])->firstWhere('type', 'Trailer')
                ?? collect($data['videos']['results'])->first();
            if ($trailer && $trailer['site'] === 'YouTube') {
                $trailerUrl = 'https://www.youtube.com/watch?v=' . $trailer['key'];
            }
        }

        $movie = Movie::updateOrCreate(
            ['tmdb_id' => $tmdbId],
            [
                'title' => $data['title'],
                'slug' => $slug,
                'overview' => $data['overview'] ?? null,
                'tagline' => $data['tagline'] ?? null,
                'trailer_url' => $trailerUrl,
                'poster_path' => $data['poster_path'] ?? null,
                'backdrop_path' => $data['backdrop_path'] ?? null,
                'release_date' => $data['release_date'] ?: null,
                'runtime' => $data['runtime'] ?? null,
                'vote_average' => $data['vote_average'] ?? 0,
                'vote_count' => $data['vote_count'] ?? 0,
                'popularity' => $data['popularity'] ?? 0,
                'original_language' => $data['original_language'] ?? null,
                'status' => $data['status'] ?? null,
                'images' => $data['images'] ?? null,
            ]
        );

        $genres = $data['genres'] ?? [];
        if (!empty($data['adult'])) {
            $genres[] = ['id' => 99999, 'name' => 'Adult'];
        }

        $this->syncGenres($genres, $movie, 'movie');
        $this->syncCast($data['credits']['cast'] ?? [], $movie);

        return $movie->fresh(['genres', 'cast']);
    }

    public function importTvShow(int $tmdbId, ?string $language = null): TvShow
    {
        $params = $language ? ['language' => $language] : [];
        $data = $this->client->getTvShow($tmdbId, $params);

        $baseSlug = Str::slug($data['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (TvShow::where('slug', $slug)->where('tmdb_id', '!=', $tmdbId)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $trailerUrl = null;
        if (!empty($data['videos']['results'])) {
            $trailer = collect($data['videos']['results'])->firstWhere('type', 'Trailer')
                ?? collect($data['videos']['results'])->first();
            if ($trailer && $trailer['site'] === 'YouTube') {
                $trailerUrl = 'https://www.youtube.com/watch?v=' . $trailer['key'];
            }
        }

        $tvShow = TvShow::updateOrCreate(
            ['tmdb_id' => $tmdbId],
            [
                'name' => $data['name'],
                'slug' => $slug,
                'overview' => $data['overview'] ?? null,
                'tagline' => $data['tagline'] ?? null,
                'trailer_url' => $trailerUrl,
                'poster_path' => $data['poster_path'] ?? null,
                'backdrop_path' => $data['backdrop_path'] ?? null,
                'first_air_date' => $data['first_air_date'] ?: null,
                'last_air_date' => $data['last_air_date'] ?: null,
                'number_of_seasons' => $data['number_of_seasons'] ?? 0,
                'number_of_episodes' => $data['number_of_episodes'] ?? 0,
                'episode_run_time' => ! empty($data['episode_run_time']) ? $data['episode_run_time'][0] : null,
                'vote_average' => $data['vote_average'] ?? 0,
                'vote_count' => $data['vote_count'] ?? 0,
                'popularity' => $data['popularity'] ?? 0,
                'original_language' => $data['original_language'] ?? null,
                'status' => $data['status'] ?? null,
                'type' => $data['type'] ?? null,
                'images' => $data['images'] ?? null,
            ]
        );

        $genres = $data['genres'] ?? [];
        if (!empty($data['adult'])) {
            $genres[] = ['id' => 99999, 'name' => 'Adult'];
        }

        $this->syncGenres($genres, $tvShow, 'tv');
        $this->syncCast($data['credits']['cast'] ?? [], $tvShow);

        foreach ($data['seasons'] ?? [] as $seasonData) {
            $this->importSeason($tvShow, $seasonData['season_number'], $language);
        }

        return $tvShow->fresh(['genres', 'cast', 'seasons.episodes']);
    }

    public function importSeason(TvShow $tvShow, int $seasonNumber, ?string $language = null): Season
    {
        $params = $language ? ['language' => $language] : [];
        $data = $this->client->getTvSeason($tvShow->tmdb_id, $seasonNumber, $params);

        $season = Season::updateOrCreate(
            ['tmdb_id' => $data['id']],
            [
                'tv_show_id' => $tvShow->id,
                'season_number' => $data['season_number'],
                'name' => $data['name'],
                'overview' => $data['overview'] ?? null,
                'poster_path' => $data['poster_path'] ?? null,
                'air_date' => $data['air_date'] ?: null,
                'episode_count' => count($data['episodes'] ?? []),
            ]
        );

        foreach ($data['episodes'] ?? [] as $episodeData) {
            $season->episodes()->updateOrCreate(
                ['tmdb_id' => $episodeData['id']],
                [
                    'episode_number' => $episodeData['episode_number'],
                    'name' => $episodeData['name'],
                    'overview' => $episodeData['overview'] ?? null,
                    'still_path' => $episodeData['still_path'] ?? null,
                    'air_date' => $episodeData['air_date'] ?: null,
                    'runtime' => $episodeData['runtime'] ?? null,
                    'vote_average' => $episodeData['vote_average'] ?? 0,
                    'vote_count' => $episodeData['vote_count'] ?? 0,
                ]
            );
        }

        return $season->fresh('episodes');
    }

    public function syncAllGenres(?string $language = null): void
    {
        $params = $language ? ['language' => $language] : [];
        foreach (['movie', 'tv'] as $type) {
            $data = $this->client->getGenres($type, $params);

            foreach ($data['genres'] ?? [] as $genre) {
                Genre::updateOrCreate(
                    ['tmdb_id' => $genre['id']],
                    [
                        'name' => $genre['name'],
                        'slug' => Str::slug($genre['name']),
                    ]
                );
            }
        }
    }

    /**
     * @param  int[]  $tmdbIds
     * @return Collection<int, Movie|TvShow>
     */
    public function importBulk(array $tmdbIds, string $type = 'movie', ?string $language = null): Collection
    {
        $results = collect();

        foreach ($tmdbIds as $tmdbId) {
            $results->push(
                $type === 'movie'
                    ? $this->importMovie($tmdbId, $language)
                    : $this->importTvShow($tmdbId, $language)
            );
        }

        return $results;
    }

    /**
     * @param  array<int, array{id: int, name: string}>  $genres
     */
    private function syncGenres(array $genres, Movie|TvShow $model, string $type): void
    {
        $genreIds = [];

        foreach ($genres as $genreData) {
            $genre = Genre::updateOrCreate(
                ['tmdb_id' => $genreData['id']],
                [
                    'name' => $genreData['name'],
                    'slug' => Str::slug($genreData['name']),
                ]
            );
            $genreIds[] = $genre->id;
        }

        $model->genres()->sync($genreIds);
    }

    /**
     * @param  array<int, mixed>  $castMembers
     */
    private function syncCast(array $castMembers, Movie|TvShow $model): void
    {
        $model->cast()->delete();

        $castToInsert = array_slice($castMembers, 0, 20);

        foreach ($castToInsert as $index => $member) {
            $model->cast()->create([
                'tmdb_id' => $member['id'],
                'name' => $member['name'],
                'character' => $member['character'] ?? null,
                'profile_path' => $member['profile_path'] ?? null,
                'order' => $member['order'] ?? $index,
            ]);
        }
    }
}
