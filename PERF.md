# Performance Ledger

Measured attempts, kept and reverted alike. Read before proposing an experiment — don't re-run a failed idea.

## Dates

All measurements: 2026-09-02, local dev (MySQL, PHP 8.5, Next.js 16.3.1).

## Backend audit (no changes made — nothing to fix)

DB scale: 8 movies, 5 TV shows, 22 seasons, 418 episodes. Query/timing probe (Pest, cold + warm cache):

| Endpoint | Queries cold | Time cold | Time warm |
|---|---|---|---|
| GET /browse | 8 | 44 ms | 1.7 ms (cache hit) |
| GET /movies | 1 | 7 ms | 1.5 ms (cache hit) |
| GET /movies?genre= | 1 | 7 ms | 1.2 ms (cache hit) |
| GET /search | 2 | 2.5 ms | 1.7 ms |
| GET /genres | 1 | 34 ms | 1.4 ms |
| GET /history/continue-watching | 1 | 5.4 ms | 1.4 ms |
| GET /activity-feed | 1 | 2.1 ms | 1.3 ms |
| GET /watchlist | 1 | 2.6 ms | 1.3 ms |

No N+1, no missing indexes, no slow query. Caches (`browse_rows`, `movies_index_*`) hit on warm pass. Backend is not a bottleneck at any realistic dataset size for this app. **Decision: no backend change.** Re-measure when catalog grows past ~10k rows or p95 exceeds ~100 ms.

## Client image sizes (kept)

Bottleneck: TMDB served at oversized variants. Hero backdrops at `original` (up to 1.8 MB), posters at `w500` (~57-99 KB) for 140-180 px display slots.

Measured bytes (HEAD on real catalog images):

| Swap | Before | After | Saving |
|---|---|---|---|
| Hero backdrop `original` → `w1280` | 240-1104 KB | 109-170 KB | 55-85% |
| Poster `w500` → `w342` | 57-99 KB | 31-52 KB | 46-48% |
| Admin still `w500` → `w300` | ~57 KB | ~31 KB | ~46% |

`w342` chosen over `w185` for posters: covers a 180 px card at 2x DPR (≈360 px) with no visible softness; `w185` shaves more bytes but looks soft on retina. Hero gets `fetchPriority="high"` on the active LCP slide.

Scope: all user-facing + admin thumbnails (16 files). Remaining `original`/`w500` calls (`ImagesTab` preview modal, gallery grid, hero logo) are intentional — full-res preview needs original; logo is small.
Verify: client `tsc --noEmit` clean, `next build` passes, backend suite 46/47 (1 pre-existing failure, unrelated). Bundle size unchanged (string-only diff).

## Reverted / not tried

- React.memo / useMemo pass: skipped. No measured INP issue; the lint error in `app/(admin)/layout.tsx` (setState-in-effect) is a separate code-smell, not a measured perf problem.
- `next/image` migration: skipped. TMDB is a remote CDN with `original` hot-linking; sizing URLs is free (no build-time optimizer, no extra origin round trip). Revisit if a self-hosted image pipeline is added.
- Backend caching beyond what exists: skipped. Warm responses are ~1.5 ms; a cache layer adds staleness + eviction for nothing.

## Guards

- Client: no perf budget in CI exists (no CI). The image-size fix is enforced by code review habit only. Add `web-vitals` RUM + Lightroom CI budget before launch if LCP budget matters.
- Backend: re-run the probe (see Backend audit) if catalog grows significantly.