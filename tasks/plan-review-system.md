# Implementation Plan: Full Review System

## Overview

Add a complete user-facing review system on top of the existing (backend-only, minimal)
review/rating feature:

- **Public UI**: a Reviews section on movie & TV detail pages — average rating + count,
  a list of other users' reviews, and a create/edit/delete form for the signed-in user.
- **Moderation**: an admin "Reviews" panel to hide/unhide (unapprove/approve) reviews and
  delete abusive ones. Hidden reviews are excluded from the public average/count/list.
- **Backend**: add an `is_approved` moderation flag, an average-rating + my-review payload
  on the per-title endpoint, and admin list/approve/hide/delete endpoints.

## Contract decisions

- **Auto-approve on create** (default `is_approved = true`). Users see their review
  immediately; admins **hide** problematic ones. This avoids the confusing "I posted a
  review and it vanished" state of approve-first moderation. Admin can still fully
  moderate. Flag if you want review-first approval instead. (This is the one open choice.)
- **Public per-title endpoint reuses the existing route**
  `GET /reviews/{media_type}/{id}` (now `{media_type}`/`{id}`), extended to return an
  aggregate bucket + only-approved reviews. The detail pages already have the media `id`.
- Routing stays `v1`, consistent with the existing-app convention.

## Backend (Laravel)

### Migration
- Add `is_approved` (boolean, `default(true)`) to `reviews`.

### ReviewData
- Add `is_approved`, and drop `reviewable`... keep current fields; add `is_approved`.

### ReviewController
- `store`/`update`: leave default approve behavior.
- `forTitle(mediaType, id)`: return aggregate bucket:
  `{ media_id, media_type, avg_rating, review_count, my_review, reviews[] }`
  where `avg_rating`/`review_count`/`reviews[]` count **only approved** reviews, and
  `my_review` is the requesting user's review (if any). `with('user')`.

### Admin ReviewController (new, `Admin\ReviewController`)
- `index`: paginated list (`with(['user','reviewable'])`), filterable by `is_approved`,
  `search` on user name/body, sort by created_at/rating. Returns `ReviewData`-shaped rows.
- `approve(Review)`: set `is_approved = true`.
- `hide(Review)`: set `is_approved = false`.
- `destroy(Review)`: delete.
- All under `admin` + `EnsureUserIsAdmin` group in `routes/api.php`.

### Routes (`routes/api.php`)
- Already: `POST /reviews`, `PUT|DELETE /reviews/{review}`, `GET /reviews/{media_type}/{id}`.
- Add admin: `GET /admin/reviews`, `POST /admin/reviews/{review}/approve`,
  `POST /admin/reviews/{review}/hide`, `DELETE /admin/reviews/{review}`.

### Tests
- Update `ReviewApiTest`: moderation field + aggregate payload shape.
- New `AdminReviewApiTest`: list, approve, hide, delete, only approved shown.

## Frontend (Next.js client)

### Public — detail pages (server components)
- Movie `app/(main)/movie/[slug]/page.tsx` + TV `app/(main)/tv/[slug]/page.tsx`:
  add a server helper `getReviews(mediaType, id)` → `fetchApi('/reviews/{type}/{id}')`
  → pass bucket to a new client `<ReviewsSection>` between Cast and More Like This.
- New `components/media/ReviewsSection.tsx` (client):
  - Shows `avg_rating` + `review_count`.
  - Lists approved reviews (user name, rating, body, date).
  - If signed in (`useAuth().user`): a rating selector (1–10) + textarea to create; if the
    user already has a review, prefill + allow edit/delete. Mutations via new server action.
- New server action `app/actions/reviews.ts`: `submitReviewAction`, `deleteReviewAction`
  (Conventional-mutation pattern: `fetchApi` POST/PUT/DELETE, `revalidatePath`).

### Admin — reviews panel
- `app/(admin)/admin/reviews/page.tsx` (async) → `fetchAdminPage('/admin/reviews')` →
  `ReviewsClient`.
- `app/(admin)/admin/reviews/ReviewsClient.tsx`: TanStack Query table; actions per row:
  Hide/Show (toggle approve), Delete. Filter by status (All / Hidden / Visible).
- Server actions in `app/actions/admin-reviews.ts`: `approveReviewAction`,
  `hideReviewAction`, `deleteReviewAction` (or reuse admin-content).
- Wire nav: add "Reviews" to `contentItems` + `contentActive` + `pageTitle` in
  `app/(admin)/layout.tsx`.

## Verification
- `php artisan test --compact` (server) green after each backend change.
- `vendor/bin/pint --format agent`.
- Client: `npx tsc --noEmit`, `npm run lint` (no new errors), `npm run build`.
- Manual: post a review on a movie → shows + updates average; admin hides it → gone from
  public avg/list; admin re-shows.
## Scope (per user)

In addition to reviews, the user chose **Reviews + Comments** in the same pass. Comments are a
separate thread/reply feature on movie & TV detail pages with the same moderation model
(auto-approve on create, admin hide/unhide, delete), sharing the `ModerationClient` admin panel.

## Non-goals

- No DB re-normalization of the polymorphic `reviewable_*` / `commentable_*` columns.
- No email notifications.
- Comments/reviews attach to Movie & TvShow only (not Episode), consistent with the existing
  review contract.

## Tasks

1. ✅ Migration for `is_approved` + comment table + model/factory/data/controllers + routes.
2. ✅ Backend: `ReviewController::forTitle` aggregate + `CommentController` + admin controllers.
3. ✅ Tests (ReviewApi, CommentApi, AdminModerationApi).
4. ✅ Client: reviews server action + `ReviewsSection`; comments server action + `CommentsSection`; wired into movie/tv pages.
5. ✅ Client: admin `Reviews` + `Comments` panels via shared `ModerationClient` + nav wiring.
6. ✅ Verify: server tests (69), pint, tsc, lint (0 errors), build.