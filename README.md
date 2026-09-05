# Streamku — API Server

[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel)](https://laravel.com/)
[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?logo=php)](https://www.php.net/)
[![Sanctum](https://img.shields.io/badge/Auth-Sanctum-6D28D9)](https://laravel.com/docs/sanctum)
[![Pest](https://img.shields.io/badge/Tests-Pest-F8BC45?logo=pest)](https://pestphp.com/)

 REST API for **Streamku**, a movie & TV streaming catalog. Versioned under `/api/v1`, token auth via Sanctum, SQLite by default, TMDB + OpenSubtitles integrations, chunked tus uploads, and Reverb realtime for watch parties.

> Frontend lives in [`../client`](../client).

## Domain glossary

Use these terms (and avoid the alternatives):

| Term | Meaning | Avoid |
| ---- | ------- | ----- |
| Movie / TvShow / Season / Episode | Film, series, numbered season group, numbered installment (smallest playable unit) | Show, series, Actor |
| Cast | Credited people for a title | Person, Crew |
| Video | Playable source entry on a Movie or Episode | Source, file |
| Media | Stored file (video, subtitle, image) with disk location, quality, size | Asset, storage |
| Watchlist / Favorite | Save-for-later collection / explicitly liked titles | Queue, Bookmark |
| WatchHistory | Per-title playback progress record | — |
| Review / WatchParty / Friend | Rating + opinion / realtime group session / two-way connection | — |
| Upload / Import | Chunked upload session / bringing a title in from an external catalog | Transfer |

## Features

| Area | Endpoints |
| ---- | --------- |
| Auth | Register, login, logout, me, profile update |
| Browse | `/browse`, `/search`, movies & TV lists, detail, recommendations, season/episode lookup |
| Genres & cast | Public genre catalog, cast listing |
| Library | Watchlist & favorites CRUD |
| History | Progress heartbeats, continue-watching feed |
| Social | Reviews & comments (public read, auth write), friends, activity feed |
| Watch parties | Create, join, playback sync (Reverb realtime) |
| Streaming | Media manifest per title/episode, file streaming, subtitles |
| Admin | Analytics overview, movie/TV/genre/cast CRUD, TMDB search/import/preview, image upload, season/episode management, embed videos, bulk episode generation, review/comment moderation |
| Uploads | Resumable tus endpoint for large video files |

## Tech stack

- **Laravel 13** + **PHP 8.3+**
- **Sanctum** API tokens, **Eloquent API Resources**, versioned `Api\V1` controllers
- **SQLite** default (swap via `DB_*`), database queue + cache
- **TMDB** metadata ingestion, **OpenSubtitles** subtitles
- **laravel-tus** uploads, **Reverb** websockets, **Pulse** monitoring
- **Pest** tests, **Pint** formatting, **Boost** agent rules

## Prerequisites

- PHP 8.3+ with `sqlite` extension
- Composer 2+
- External keys only if you use ingestion: `TMDB_API_KEY`, `OPENSUBTITLES_API_KEY`

## Quickstart

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
composer dev        # serve :8000 + queue worker + vite
```

API base: `http://localhost:8000/api/v1`.

| Variable | Default | Purpose |
| -------- | ------- | ------- |
| `DB_CONNECTION` | `sqlite` | Database driver |
| `TMDB_API_KEY` / `TMDB_BASE_URL` | — / `https://api.themoviedb.org/3` | Title metadata ingestion |
| `TMDB_IMAGE_BASE_URL` | `https://image.tmdb.org/t/p` | Poster/backdrop URLs |
| `OPENSUBTITLES_API_KEY` / `OPENSUBTITLES_BASE_URL` | — | Subtitle search |

Useful commands:

```bash
php artisan route:list --path=api   # inspect endpoints
php artisan test --compact          # Pest suite
vendor/bin/pint --dirty             # format changed PHP files
```

## Project structure

```
app/Http/Controllers/Api/V1/  # versioned controllers (Auth, Browse, Movie, TvShow, Admin/*, ...)
routes/api.php                # all /v1 routes (public, auth:sanctum, admin groups)
config/                       # services (tmdb, opensubtitles), filesystems, queue
database/                     # migrations, factories, seeders
tests/                        # Pest feature + unit tests
docs/                         # adr/, agents/
CONTEXT.md                    # domain glossary (Movie, Season, Video, Watchlist, ...)
PERF.md                       # performance notes
```

Admin routes sit under `/admin` behind `EnsureUserIsAdmin`; public catalog reads work signed out.

## Related docs

- [`../client`](../client) — frontend setup and conventions
- [`AGENTS.md`](AGENTS.md) — agent working rules (Boost, Pest, Pint)
