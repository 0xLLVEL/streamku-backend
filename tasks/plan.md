# Implementation Plan: Streamku API polymorphic-contract redesign

## Overview

Streamku's API exposes one polymorphic concept ("a Media: movie/tv_show/episode") under at
least five different field names and several loosely-validated value vocabularies. This
redesign unifies the **wire contract** around a single pair — `media_type` + `media_id` —
with one strict vocabulary (`movie` | `tv_show` | `episode`), while leaving the database
columns untouched (they are an implementation detail, not part of the contract; see
"Validate at Boundaries / don't leak internals").

It also fixes a real response-shape bug (double-wrapped recommendations) and collapses the
crappy `POST /history` + `PATCH /history/sync` "call POST first or get a 404" dance into a
single idempotent write.

The redesign is **coordinated**: the server and the Next.js client
(`server/client/`) change in lockstep, and server tests are updated for every change.

## Principles applied (from interface-design skill)

- **Validate at boundaries only**: `MediaType` is the single vocabulary gate. Wire in
  `movie|tv_show|episode`, stored as model FQCN. Translation happens once at the boundary
  (request DTOs + response DTOs). DB columns are not re-validated.
- **One vocabulary, one name**: the same concept is never `watchable_*`, `favoritable_*`,
  `watchlistable_*`, `reviewable_*`, `mediable_*` on the wire.
- **Prefer addition over modification** where a client already depends on a shape that is
  fine; **fixed hard** where the shape is wrong (double-wrap, sync/404 dance).
- **One atomic idempotent write** for state changes that get retried (history heartbeat).

## Target wire contract

**Request bodies** (user writes the media): use `media_type` (enum value) + `media_id`.

| Endpoint | Was | Becomes |
|---|---|---|
| `POST /history` | `watchable_type`,`watchable_id`,... | `media_type`,`media_id`,... |
| `PATCH /history/sync` | (separate endpoint) | **removed** — merged into `POST /history` |
| `POST /watchlist` | `watchlistable_id`,`watchlistable_type` | `media_id`,`media_type` |
| `POST /favorites` | `favoritable_id`,`favoritable_type` | `media_id`,`media_type` |
| `POST /reviews` | `reviewable_id`,`reviewable_type` | `media_id`,`media_type` |
| `POST /watch-parties` | `mediable_id`,`mediable_type` | `media_id`,`media_type` |
| `POST /admin/uploads/initiate` | `mediable_id`,`mediable_type` | `media_id`,`media_type` |

Values accepted for `media_type` everywhere: strictly `movie | tv_show | episode`
(`MediaType::fromString` tightened; no more `Movie`/`tv`/`tv-show` fuzz).

**Responses already emit** `media_type`+`media_id` (ReviewData, WatchHistoryData,
WatchlistData, FavoriteData, WatchPartyData) — no DTO output change needed, only the
`fromModel` mapping is already correct.

**Response shape fix**: `GET /movies/{slug}/recommendations` and
`GET /tv-shows/{slug}/recommendations` currently double-wrap
(`{data:{data:[...]}}`). Fix to a plain list `{data:[...]}`. Client reads `json.data?.data`
→ `json.data`. This is a deliberate breaking change, coordinated client-side.

**Routes**: `GET /reviews/{type}/{id}` → `GET /reviews/{media_type}/{id}` (path param
renamed for clarity; paves the way for a future nested sub-resource but is not consumed by
the client today, so no client work).

## Non-goals

- No DB column/migration changes (`watchable_*` etc. stay). The morph columns are an
  implementation detail.
- No renaming of `mediable_*` inside the admin media/upload machinery beyond the request
  wire fields listed above.
- The `GET /api/v1/cast` placement quirk (authed admin controller outside admin group) is
  left as-is per PROJECT-MAP gotcha — low value, real risk.
- No bulk delete endpoint, no `Idempotency-Key` header. Watch-party join already
  idempotent via unique constraint; history upsert is the idempotent path.

## Task list

### Phase 1: MediaType as single gate (foundation)
- [x] Task 1: Tighten `MediaType::fromString` to one strict vocabulary. Add `fromStringStrict`. Update `MediaTypeEnumTest`.

### Phase 2: Server contract + response-shape fixes (backend-only)
- [x] Task 2: Merge history write into single `POST /history` upsert; remove `PATCH /history/sync` route + method. Update `HistoryApiTest`.
- [x] Task 3: Rename request DTO fields to `media_type`/`media_id` for history, review, watchlist, favorite, watch party, upload-initiate. Update validation rules.
- [x] Task 4: Fix `recommendations` double-wrap in Movie + TvShow controllers. Update `MovieApiTest`/`TvShowApiTest`.
- [x] Task 5: Rename `reviews/{type}/{id}` → `reviews/{media_type}/{id}`. Update `ReviewApiTest`.

### Checkpoint A
- [x] `php artisan test --compact` green (51/51)
- [x] `vendor/bin/pint --format agent` clean

### Phase 3: Client coordination (server/client)
- [x] Task 6: Update `client/lib/watchHistory.ts` to single `POST /history` with `media_type/media_id`.
- [x] Task 7: Update `client/hooks/useResourceToggle.ts` (+ callers) to `media_id/media_type`.
- [x] Task 8: Update recommendations readers `json.data?.data` → `json.data`.
- [x] Task 9: Update any upload-initiate caller + genre/review references. (Verified: no client code called `/admin/uploads/initiate`, `history/sync`, or the renamed fields — none to change.)

### Checkpoint B
- [x] `npm run build` in client succeeds
- [x] `npm run lint` (0 errors), `npx tsc --noEmit` clean

## Risks and mitigations

| Risk | Impact | Mitigation |
|---|---|---|
| Client consumes a changed field and breaks at runtime | High | Client changes land in same session; manual + build verification after |
| `success()` double-detects paginator shape on plain arrays | Med | Do not return arrays containing both `data`+`current_page`; use Data objects/collections |
| Tests don't cover a renamed field | Med | Update every affected feature test; run full suite at Checkpoint A |
| Breaking `POST /history` shape the client already forks on | High | Single coordinated change; delete `sync` atomically with client update |

## Open questions
- None blocking. Confirm the target contract (esp. deleting `PATCH /history/sync`) before Phase 2/3.
