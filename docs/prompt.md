ت

---

# 🔧 Ultimate Build Prompt — **Jidyan** (PHP 8.3 / Laravel 11 / Nginx / PostgreSQL / Redis / FFmpeg-HLS)

**Role:** You are a senior full-stack architect & implementer. Build a production-ready football talent platform named **Jidyan** for players, coaches, clubs, and agents.
Constraints: **Web stack = PHP 8.3 (Laravel 11)**, deploy on a single **VPS** with **Nginx**. **PostgreSQL** database. **No paid video services**: self-host uploads, transcode with **FFmpeg** to **HLS** and serve via Nginx. Bilingual (**ar/en**, RTL). Low-bandwidth friendly. Start with a robust MVP (no AI) but keep design extensible.

---

## 0) Tech Stack & Conventions

* **Framework:** Laravel 11 (PHP 8.3), Composer PSR-4, strict types.
* **Frontend:** Blade + TailwindCSS + Alpine.js (خفيف وسريع) + Laravel Livewire 3 للصفحات التفاعلية (لوحة التحكم والبحث).

  > بدون SPA ثقيلة لتناسب الشبكات الضعيفة.
* **API:** Laravel API routes (`/api/v1/*`) لتطبيقات مستقبلية (موبايل).
* **DB:** PostgreSQL + Laravel Migrations/Seeders.
* **Auth:** Laravel Breeze (session + API tokens with Sanctum). 2FA اختياري.
* **Queues/Jobs:** Laravel Queues (Redis + Horizon).
* **Search:** PostgreSQL indices + simple full-text (TSVECTOR) في MVP.
* **Uploads:** Chunked uploads (Tus/Dropzone fallback). تخزين على القرص (`storage/app/media`) ثم تحويل HLS بخلفية Queue.
* **Video:** FFmpeg لتحويل HLS (m3u8 + .ts) بجودات 240/360/480/720.
* **Serve Media:** Nginx static for `/media/hls/*` + secure signed URLs.
* **Obs/Logs:** Laravel Telescope (staging only), Monolog JSON logs. `/health` route.
* **CI/CD:** GitHub Actions (lint + phpstan + pest tests + build assets + rsync/ssh deploy script).
* **Security:** CSRF, rate limit، validation، content moderation، RBAC، feature flags.

---

## 1) Roles & Permissions (RBAC)

**Roles:** `player`, `coach`, `club_admin`, `agent`, `verifier`, `admin`.

* Player: profile/media/stats, apply to opportunities, link/unlink agent.
* Coach: search/filter players, shortlists, invites, messages.
* Club Admin: manage club, post opportunities, review applicants.
* Agent: link to players (approval flow), manage player profile (scoped), respond to invites, private notes.
* Verifier: KYC-lite (ID/academy) approvals, media moderation.
* Admin: full moderation, reports, feature flags.
  Use **spatie/laravel-permission**.

---

## 2) Core Features (MVP)

### A) Player Profile (Sports CV)

Fields: name, dob, nationality, city, height_cm, weight_kg, position, preferred_foot, current_club, previous_clubs[], bio, injuries[], achievements[], visibility.
Media: images + short videos (≤ 60s, ≤ 5 per player). Store provider=`local`, `path`, `duration`, `quality`, `poster_path`.
Manual stats: season, matches, goals, assists, notes, verified_by (nullable).
Badges: `verified_identity`, `verified_academy`.

### B) Search & Filters (Coach)

Filter by: position, age range, city/country, height/weight ranges, preferred foot, availability, badges, has_video, last_active.
Sort: newest / most_viewed / top_rated.
Shortlists with private notes.

### C) Opportunities (Tryouts/Jobs)

Club posts opportunity (title, description, requirements JSON, location, deadline).
Player/Agent applies attaching media + note.
Pipeline: received → shortlisted → invited → rejected → signed.

### D) Messaging & Notifications

In-app messaging threads (player↔coach/club/agent) + email notifications + optional SMS adapter.
Real-time via Livewire polling (no websockets لازم).

### E) Verification & Moderation

KYC-lite (ID/letter PDF). Verifier workflow: pending/approved/rejected(+reason).
Content reporting & moderation queue.

### F) Media Pipeline (Self-Hosted)

* Upload endpoint accepts chunked uploads → store under `storage/app/media/inbox/{uuid}`.
* Dispatch job: **Transcode to HLS** with FFmpeg into `storage/app/media/hls/{media_id}/{quality}/index.m3u8`.
* Generate poster `.jpg` (frame at 1s).
* Move original to `storage/app/media/archive/{media_id}.mp4` (optional after 30 days).
* Serve playback via Nginx static path `/media/hls/...` using signed route `/media/signed-url/{media_id}`.

**FFmpeg example (720p):**

```bash
ffmpeg -i input.mp4 -vf "scale=-2:720" -c:v h264 -profile:v main -crf 23 -preset veryfast -c:a aac -b:a 128k \
  -f hls -hls_time 4 -hls_list_size 0 -hls_segment_filename "seg_%04d.ts" index.m3u8
```

**Qualities:** 240p, 360p, 480p, 720p (generate renditions in parallel queue jobs).

---

## 3) Data Model (Migrations outline)

* `users` (name, email, phone, password, twofa_secret?, role).
* `profiles_players` (user_id FK, city, country, position, preferred_foot, height_cm, weight_kg, current_club, bio, visibility, verified_identity_at?, verified_academy_at?).
* `player_media` (player_id, type['image','video'], path, provider['local'], hls_path, poster_path, duration_sec, quality_label, status['processing','ready','failed']).
* `player_stats` (player_id, season, matches, goals, assists, notes, verified_by_user_id?).
* `coaches` (user_id, license_level?, club_id?, bio?).
* `clubs` (name, country, city, verified_at?).
* `agents` (user_id, license_no?, agency_name?, verified_at?).
* `player_agent_links` (player_id, agent_id, status['pending','active','revoked']).
* `opportunities` (club_id, title, description, requirements_json, location_city, location_country, deadline_at, status).
* `applications` (opportunity_id, player_id, media_id?, note, status, reviewed_by_user_id?).
* `shortlists` (coach_id, title), `shortlist_items` (shortlist_id, player_id, note?).
* `messages` (sender_user_id, receiver_user_id, body, read_at?).
* `verifications` (user_id, type['identity','academy'], document_path, status, reason?)
* `view_logs` (viewer_user_id?, player_id).
* `feature_flags` (key, enabled bool).
  Add indexes: `position`, `city`, `country`, `age (generated from dob)`, `has_video`.

---

## 4) HTTP & API Endpoints

* **Web** (Blade/Livewire):
  `/` (home, featured), `/players/[id]`, `/dashboard/player`, `/dashboard/coach`, `/dashboard/club`, `/dashboard/agent`, `/verify`, `/admin`.
* **API (`/api/v1`)**:
  Auth (`login/register/refresh` via Sanctum),
  Players (CRUD limited), Media (`POST /media/upload`, `POST /media/{id}/retry`, `DELETE`),
  Search (`GET /players?filters`),
  Opportunities/Applications,
  Agent links (`POST /players/{id}/agent-link`, approve/revoke),
  Messages,
  Verification queue,
  Admin reports & flags.
  Return unified JSON errors + cursor pagination.

---

## 5) UX Rules (شبكات ضعيفة)

* قبل الرفع: نصائح جودة + تحذير من واتساب المضغوط.
* حد أقصى: **5 فيديو/لاعب، 60 ثانية، 120MB**.
* واجهة جودة: Excellent/OK/Low مع نصيحة مباشرة.
* تشغيل افتراضي على 360/480 مع خيار 720.
* Resumable uploads + استئناف عند انقطاع الشبكة.

---

## 6) Environment & Secrets

**لا تضع كلمات المرور داخل الكود**—استخدم `.env`.
وفّر `.env.example`. (قيم المستخدم لديك: **DB name `Jidyan` | user `Jidyan` | pass `Khan@70990100`** — خزّنها في `.env` فقط.)

**.env example:**

```env
APP_NAME=Jidyan
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://jidyan.com

# DB (أمثلة – عرّف القيم الفعلية عند النشر)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=Jidyan
DB_USERNAME=Jidyan
DB_PASSWORD=Khan@70990100

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Media paths
MEDIA_INBOX=/var/www/jidyan/storage/app/media/inbox
MEDIA_HLS=/var/www/jidyan/storage/app/media/hls
MEDIA_ARCHIVE=/var/www/jidyan/storage/app/media/archive
```

---

## 7) Nginx Config (sample `/etc/nginx/sites-available/jidyan.conf`)

```nginx
server {
    listen 80;
    server_name jidyan.com;

    root /var/www/jidyan/public;
    index index.php;

    # Laravel app
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Serve HLS segments & posters (read-only static)
    location ^~ /media/hls/ {
        alias /var/www/jidyan/storage/app/media/hls/;
        add_header Cache-Control "public, max-age=3600";
        types { application/vnd.apple.mpegurl m3u8; video/mp2t ts; }
        expires 1h;
    }

    # PHP-FPM
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~* \.(jpg|jpeg|png|gif|svg|webp|css|js|ico)$ {
        expires 7d; add_header Cache-Control "public";
    }

    client_max_body_size 200M; # allow bigger uploads
}
```

---

## 8) Workers & Horizon (Queues)

* استخدم **Laravel Horizon** لإدارة الطوابير.
* أنشئ **systemd service** لتشغيل Horizon:

```ini
# /etc/systemd/system/horizon.service
[Unit]
Description=Laravel Horizon
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/jidyan/artisan horizon

[Install]
WantedBy=multi-user.target
```

ثم:

```bash
systemctl enable horizon && systemctl start horizon
```

Jobs:

* `ProcessUploadJob` → فحص الملف + تحريك للـinbox.
* `TranscodeHlsJob` → FFmpeg لكل جودة + إنشاء m3u8/ts + poster.
* `CleanupJob` → نقل الأصل للأرشيف بعد 30 يوم.

---

## 9) Security & Moderation

* Validation صارم لكل حقول النماذج.
* Signed URLs للوصول إلى مسارات HLS (توليد رابط صالح لمدة قصيرة).
* CSRF على الويب + Rate Limiting للـAPI.
* مراجعة فيديوهات وصور قبل النشر، نظام بلاغات.
* توثيق الوكلاء واللاعبين (KYC-lite).

---

## 10) Seeds & Fixtures

* أنشئ Seeder ينشئ أدوار وصلاحيات + 1 حساب لكل دور (player/coach/club_admin/agent/verifier/admin) + نادي تجريبي + 10 لاعبين مع صور وهمية وروابط فيديو HLS تجريبية.
* صفحة Home تعرض Featured Players & Opportunities.

---

## 11) Tests & CI

* **Pest** لاختبارات (auth, player lifecycle, upload→transcode→playback, opportunities flow).
* GitHub Actions:

  * `composer validate`, `phpstan`, `pint`, `pest`.
  * Build assets (`npm ci && npm run build`).
  * Deploy script (rsync/ssh) + `php artisan migrate --force`.

---

## 12) Acceptance Criteria

* تسجيل/دخول (ar/en، RTL)، RBAC يعمل.
* اللاعب ينشئ/يعدل بروفايله، يرفع حتى 5 فيديوهات (60s) → تتحوّل HLS وتظهر بمشغل بسيط.
* المدرب يبحث ويعمل Shortlists ويراسل.
* النادي ينشر فرصة واللاعب/الوكيل يقدّم.
* ربط لاعب بوكيل بموافقة الطرفين.
* التوثيق (هوية/أكاديمية) وشارات ظاهرة في البروفايل.
* لوحة مشرف للمراجعات والتبليغات.
* Nginx يقدّم HLS بسلاسة على سرعات ضعيفة (360/480 افتراضي).

---

**Start now**:

1. Scaffold Laravel 11 project `jidyan/`.
2. Install Tailwind/Livewire/Spatie Permission/Sanctum/Horizon.
3. Create migrations/models/controllers/policies as per schema.
4. Implement upload→queue→FFmpeg→HLS pipeline and Blade player (hls.js).
5. Build Blade/Livewire dashboards per role + public player profile.
6. Add seeders, tests, and Nginx config & `.env.example`.
7. Provide `README.md` with setup & deployment steps for Ubuntu VPS (PHP 8.3-FPM, Nginx, Postgres, Redis, FFmpeg).

--.
