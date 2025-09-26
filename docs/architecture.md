# Jidyan Platform Architecture

This document summarises the high-level architecture and implementation blueprint for the Jidyan football talent discovery platform. It is designed around Laravel 11 on PHP 8.3 with PostgreSQL, Redis and FFmpeg for a self-hosted media workflow.

## Application Modules

| Module | Description |
| --- | --- |
| Authentication & RBAC | Laravel Breeze for the interactive login/register flows, Sanctum for API tokens, and Spatie Permission for role/permission mapping (`player`, `coach`, `club_admin`, `agent`, `verifier`, `admin`). |
| Player Portfolio | Player CV, multi-lingual profile fields, manual statistics, media attachments (images & HLS-ready videos), availability & recent activity tracking with verification badges. |
| Search & Discovery | Livewire driven filters (position, age, foot, city/country, height/weight, availability, verification status) with PostgreSQL indices and TSVECTOR search. |
| Opportunities & Applications | Club admins publish tryouts, players/agents apply, pipeline statuses tracked with notifications. |
| Messaging | Lightweight conversation threads (player-coach, club-player, agent-player) powered by Livewire polling for near real-time UX. |
| Verification & Moderation | Verifier dashboards for KYC-lite document review, media moderation queue, admin overview for reports & feature flags. |
| Media Pipeline | Chunked uploads saved to `storage/app/media/inbox`, asynchronous FFmpeg transcoding jobs producing 240p-720p HLS outputs served through Nginx. |
| Notifications | Laravel notifications for email, optional SMS channel abstraction, in-app notification centre. |

## Key Packages

```json
{
    "require": {
        "php": "^8.3",
        "laravel/framework": "^11.0",
        "laravel/sanctum": "^4.0",
        "laravel/horizon": "^5.0",
        "livewire/livewire": "^3.0",
        "spatie/laravel-permission": "^6.0",
        "spatie/laravel-medialibrary": "^11.0",
        "spatie/laravel-activitylog": "^5.0",
        "spatie/laravel-translatable": "^6.0"
    }
}
```

## Domain Model Overview

![Domain Model](./diagrams/domain-model.png)

> Generate ER diagram with the entities listed in the data model section when design tools are available.

### Important Tables

* `users` – core user table with locale preferences and soft deletes.
* `profiles_players` – one-to-one with users holding player-specific attributes, verification timestamps, and a maintained `searchable_text`/`search_vector` pair for full-text search.
* `player_media` – references processed media assets with provider metadata and status fields.
* `opportunities` & `applications` – job-style postings and player submissions with pipeline state machine.
* `messages` – simple threadless messaging for the MVP (threads can be derived from unique sender/receiver pairs).

## Media Processing Flow

1. Browser uploads chunks via tus-js-client (fallback to Dropzone). Upload API writes the assembled file into `storage/app/media/inbox/{uuid}`.
2. `ProcessUploadJob` validates duration (< 60s) and size (< 120 MB) via FFprobe.
3. For each rendition (240p, 360p, 480p, 720p) a `TranscodeHlsJob` is dispatched. Jobs build HLS playlists under `storage/app/media/hls/{media_id}/{quality}`.
4. Poster frame extracted at the 1 second mark using FFmpeg (`-ss 00:00:01 -vframes 1`).
5. Metadata persisted in `player_media` (JSON column for variant manifests). Status set to `ready` when all renditions succeed.
6. `CleanupJob` periodically archives the original upload into `storage/app/media/archive` and prunes failed/expired inbox files.
7. Frontend player uses `hls.js` with 360p default, exposing manual quality selection for higher tiers.

## Internationalisation

* `resources/lang/en/*` and `resources/lang/ar/*` cover UI strings. Use JSON translation files for Livewire messages.
* RTL support via Tailwind's `dir` variant and `@apply` utilities. Layout toggles `dir="rtl"` when `app()->getLocale() === 'ar'`.

## Security & Compliance

* Signed URLs for `/media/hls` via `URL::temporarySignedRoute`.
* Rate limiting API using `RateLimiter::for('api', ...)` with role-based allowances.
* Audit log via `spatie/laravel-activitylog` for critical state changes (verification approvals, opportunity status updates).
* Feature flags stored in `feature_flags` table, cached via Redis and exposed through a helper `feature('key')`.
* Content moderation queue ensures verifier approval before player media becomes public, with `content_reports` surfacing abuse flags across profiles, media, and opportunities.

## Deployment Workflow

1. GitHub Actions CI pipeline executes Pint (code style), PHPStan, Pest tests, and Vite build.
2. Upon success, deploy job runs `rsync` to the production VPS, then executes remote commands:
   * `php artisan down` (optional)
   * `php artisan migrate --force`
   * `php artisan horizon:terminate`
   * `php artisan config:cache && php artisan route:cache`
   * `php artisan up`
3. Horizon managed by systemd service defined in `deploy/systemd/horizon.service`.
4. Logs shipped in JSON format using Laravel's stack channel to a central logging solution (e.g., Elastic/OpenSearch) when available.

## Health & Observability

* `/health` route returns app version, queue/backlog status, cache/db connectivity.
* Horizon metrics for queue throughput.
* Use Supervisor/systemd to ensure queue workers and scheduler stay online.

## Next Steps

* Run `composer create-project laravel/laravel jidyan` locally (network access required).
* Copy configuration files from this repository into the generated Laravel app.
* Implement migrations, models, Livewire components, and tests following this architecture.
