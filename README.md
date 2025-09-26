# Jidyan Platform Blueprint

This repository now ships with an offline-friendly Laravel 11 codebase under `jidyan/` alongside the original implementation blueprint, infrastructure manifests, and environment configuration for the **Jidyan** football talent discovery platform. All framework and third-party packages are referenced in `composer.json` / `package.json` and can be installed once network access is available.

## Getting Started

1. **Install PHP & JS dependencies**
   ```bash
   cd jidyan
   composer install
   npm install
   npm run build
   ```
2. **Copy the environment template**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. **Run the migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```
4. **Queue workers & media pipeline**
   ```bash
   php artisan horizon
   ```
5. **Serve the application**
   ```bash
   php artisan serve
   ```

## Key Environment Variables

The provided `.env.example` file already includes the critical values from the prompt (PostgreSQL credentials, Redis configuration, media directories, FFmpeg paths). Update `APP_KEY`, `APP_URL`, and mail/SMS settings per deployment.

## Deployment Notes

* Use the Nginx vhost from `deploy/nginx/jidyan.conf` with PHP 8.3-FPM.
* Enable the Horizon systemd service defined in `deploy/systemd/horizon.service` for queue processing.
* Ensure FFmpeg/FFprobe are available at `/usr/bin/` or update the paths in `.env`.
* Configure a GitHub Actions pipeline to run Pint, PHPStan, Pest, and Vite build before deploying with rsync + `php artisan migrate --force`.

## Health Monitoring

* `/health` returns a JSON (or plain-text) snapshot summarising database, cache, queue, and media storage connectivity for load balancers or uptime probes.
* `/api/v1/health` exposes the same payload for API clients and mobile watchdogs.

## Moderation Tools

* Authenticated users can flag problematic player profiles, media, or opportunities via `/dashboard/reports` (web) or `/api/v1/reports` (API).
* Verifiers and admins review submissions in `/dashboard/admin/reports`, update statuses, and capture resolution notes for auditability.

## Documentation Index

| File | Description |
| --- | --- |
| `docs/architecture.md` | High-level application architecture, media pipeline, security posture, and deployment workflow. |
| `docs/database-schema.sql` | SQL representation of the domain entities to guide Laravel migrations. |
| `docs/prompt.md` | Original specification from the product team. |
| `deploy/nginx/jidyan.conf` | Production-ready Nginx configuration snippet. |
| `deploy/systemd/horizon.service` | systemd service definition for Laravel Horizon. |

## Limitations

The interactive build environment used to generate this repository does not permit outbound network access, preventing Composer/NPM downloads. The blueprint captures all required decisions so that the platform can be implemented locally or on a CI runner with full connectivity.
