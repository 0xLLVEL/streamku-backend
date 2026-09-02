# Streamku Context

The Streamku domain covers a streaming platform's catalog, playback, and social features: admins import and manage a catalog of movies and TV shows plus their encoded media files, and users save content, track progress, review titles, and watch together in real time.

## Content catalog

**Movie**:
A standalone film with its own genres, cast, ratings, and playback media.

**TvShow**:
A series organized into Seasons.
_Avoid_: Show, series

**Season**:
A numbered group of Episodes within a TvShow.

**Episode**:
A numbered installment of a Season; the smallest playable content unit.

**Genre**:
A classification tag applied to Movies and TvShows.

**Cast**:
A person credited with a role in a title (actor, director, etc.). The set of credited people for a title is referred to as its "cast".
_Avoid_: Person, Actor, Crew

**Video**:
A playable source entry attached to a Movie or Episode and presented to the client.
_Avoid_: Source, file

## Playback

**Media**:
A stored file — video, subtitle, or image — attached to a Movie, TvShow, or Episode, with a disk location, quality, and size.
_Avoid_: Asset, file, storage

**Quality**:
A resolution tier (width × height) that a video Media is encoded at.

## Personal library & activity

**Watchlist**:
A user's saved-to-watch-later collection of Movies, TvShows, or Episodes.
_Avoid_: Queue, to-watch

**Favorite**:
A user's explicitly liked titles, kept in a separate collection from the Watchlist.
_Avoid_: Bookmark, liked

**WatchHistory**:
A user's record of what they have watched, with per-title playback progress.

**Review**:
A user's rating and/or written opinion of a Movie, TvShow, or Episode.

**WatchParty**:
A real-time group watching session in which participants' playback stays synchronized.

**Friend**:
An accepted or pending two-way connection between two users.

**ActivityFeed**:
The chronological stream of a user's friends' recent actions.

## Catalog ingestion

**Upload**:
A chunked upload session that produces a Media file, progressing pending → uploading → processing → completed.
_Avoid_: Transfer, task

**Import**:
Bringing a title and its metadata into Streamku from an external catalog source.