# Project Map: Streamku Server

## What this is

A Laravel **video-streaming platform backend** (the "app", not the site) that powers the client frontend.
Domain term definitions live in the root **`CONTEXT.md`** — resolve vocabulary there, not here.
**The `client/` folder is a separate Next.js app with its own AGENTS.md — never edit it from here.** It is gitignored (`/client`).

## Stack

| Concern | Choice |
| --- | --- |
| Framework | Laravel `^13.17`, PHP `>=8.3` |
| API auth | Laravel Sanctum (`auth:sanctum`), Bearer tokens |
| API payloads | **Spatie Laravel Data** (`app/Data/`), NOT Eloquent API Resources — follow existing convention |
| Tests | Pest 5 (`php artisan test --compact`) |
| DB | SQLite local (`database/database.sqlite`) |
| Realtime | Laravel Reverb (websockets), Laravel Echo on client |
| FFmpeg | `pbmedia/laravel-ffmpeg` (video downscaling) |
| Uploads | Custom chunked uploader + TUS (`arthurpatriot/laravel-tus`, endpoint `api/v1/admin/tus`) |
| External data | TMDB API (`app/Services/TmdbClient.php`, `config/tmdb.php`) |
| Geo | `stevebauman/location` → `users.ip_address` / `users.country` |
| Monitoring | Laravel Pulse |

## Commands

- Dev (server + queue + vite): `composer run dev`
- Tests: `php artisan test --compact` (filter with `--filter=`)
- New test: `php artisan make:test --pest SomeFeatureTest`
- Fix style: `vendor/bin/pint --format agent` (required after any PHP edit)
- Routes: `php artisan route:list --path=api`
- Inspect config: `php artisan config:show <key>`
- Debug: `php artisan tinker --execute '...'` (single quotes)

## Architecture

API is single-versioned: everything under `api/v1/` in `routes/api.php`. Public endpoints (register/login, media streaming) sit outside the `auth:sanctum` group; everything else requires auth. Admin routes are nested under an `EnsureUserIsAdmin` middleware group.

### Directory map

| Path | Role |
| --- | --- |
| `app/Http/Controllers/Api/V1/` | Client-facing controllers (`UserLibraryController`, `WatchHistoryController`, `ReviewController`, `WatchPartyController`, `FriendController`, `ActivityFeedController`, `BrowseController`, `SearchController`, `MediaStreamController`, content controllers ...) |
| `app/Http/Controllers/Api/V1/Admin/` | Admin controllers (content CRUD, media, uploads, qualities, TMDB search/import, analytics) |
| `app/Data/` | Spatie Data DTOs for request payloads (`Requests/`) and responses (`MovieData`, `UploadStatusData`, ...) |
| `app/Models/` | Eloquent models |
| `app/Services/` | Domain services (`TmdbClient`, `TmdbFakeClient`, `TmdbImportService`, `ChunkedUploadService`, `PostUploadHandler`) |
| `app/Jobs/` | `ProcessVideoDownscale`, `ImportTmdbTitle` |
| `app/Events/` + `app/Listeners/` | `WatchPartySynced`, `ProcessTusUploadCompleted` |
| `app/Notifications/` | `NewEpisodeReleased` |
| `app/Enums/` | `MediaType` (`Movie`/`TvShow`/`Episode`, with `fromString()`) |
| `app/Traits/` | `ApiResponses` (`success()` / `error()`) — base `Controller` uses it; all API controllers extend it |

## Key flows

- **Chunked upload** (client-facing admin route): `UploadController::initiate` → upload chunks → `complete` → `ChunkedUploadService` merges chunks → `PostUploadHandler::createAndProcess` creates `Media` (videos on `local` disk; subtitles/images on `public`) → `ProcessVideoDownscale` job → if first episode video, `NewEpisodeReleased` notifies TvShow watchlist users. Alternatively the TUS endpoint handles uploads and the `ProcessTusUploadCompleted` listener follows the same create+process path.
- **TMDB import**: `TmdbImportService` fetches catalog + casting via `TmdbClient` (mockable `TmdbFakeClient`), dispatches `ImportTmdbTitle` jobs, syncs genres.
- **Streaming**: `MediaStreamController` serves `media/stream` (range requests on the actual file) plus public movie/episode media metadata endpoints.
- **Watch party sync**: posted per party → `WatchPartySynced` event → Reverb broadcast.

## API response convention

Controllers return `$this->success($payload)` / `$this->error($message, $code)` (see `app/Traits/ApiResponses.php`). Shape: `{ "success": true, "data": ..., "meta": {pagination} }`. Paginator results are unwrapped into `data` + `meta` (do NOT double-wrap).

## Routing convention

Route model bindings use explicit, human-readable keys:
`movies/{movie:slug}`, `tv-shows/{tvShow:slug}`, `tv-shows/{tvShow:slug}/seasons/{season:season_number}/episodes/{episode:episode_number}/media`, `uploads/{upload:upload_id}`, `media/{media}` (id), `users/{user}` (id).

## Gotchas

- `routes/api.php` quirk: `GET /api/v1/cast` (authed, outside admin group) is handled by `Admin\CastController::index`. Don't "fix" the placement casually.
- `UserLibraryController` handles **both** `/watchlist` and `/favorites` via `defaults('library', 'watchlist'|'favorites')` — keep the defaults on any new route you add there.
- Videos are on the `local` disk (not URL-accessible); the Media `url` append only resolves for public-disk items. Streaming goes through the streaming controller.
- Index queries are cached with `Cache::remember` keyed on the serialized request (e.g. `MovieController::index`). Invalidate/adjust the key when changing filters to avoid stale responses.
- `MediaType` includes `TvShow`, but uploads only accept Movie/Episode targets — know which path you're on before widening either.
- Don't add new dependencies or base folders without approval — this app has a fixed structure (Laravel Boost rule).
- Always run `vendor/bin/pint --format agent` after PHP edits; tests must be written/updated for every change.