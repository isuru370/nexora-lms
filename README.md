# NexoraLMS

Multi-tenant LMS for Sri Lankan tutors. Every teacher gets their own site (`kasun.nexoralms.lk`) with classes, YouTube lessons, Zoom live classes and monthly fee collection.

Laravel 13 · PHP 8.3+ · MySQL 8 · Blade + Tailwind CSS v4 + Alpine.js

## Sites

| Who | URL (local) |
|---|---|
| Central site | `http://app.nexoralms.localhost` |
| Super Admin | `http://app.nexoralms.localhost/admin` |
| Teacher site (students) | `http://kasun.nexoralms.localhost` |
| Teacher panel | `http://kasun.nexoralms.localhost/manage` |

The base domain comes from `APP_BASE_DOMAIN` in `.env`. Nothing else hard-codes a domain.

## Local setup (WAMP)

1. PHP 8.3+, Composer, Node 20+.
2. Apache virtual host for `nexoralms.localhost` with `ServerAlias *.nexoralms.localhost`, `DocumentRoot` = `public/`.
3. Create a MySQL database `nexora_lms` (`utf8mb4_unicode_ci`).
4. Then:

```bash
composer install
copy .env.example .env        # cp on Linux/macOS
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
```

Demo logins after `--seed` (password is `password` for all):

| Where | Login |
|---|---|
| `app.nexoralms.localhost/admin` | `admin@nexoralms.test` |
| `kasun.nexoralms.localhost/manage` | `077 000 0001` |
| `nimal.nexoralms.localhost/manage` | `077 000 0002` |
| `kasun.nexoralms.localhost/login` | `071 000 0001` |

Create a real Super Admin with `php artisan nexora:create-super-admin`.

## Deploy and production check

```bash
php artisan nexora:deploy     # every deploy (Coolify post-deployment command)
php artisan nexora:doctor     # check readiness any time; --production to use production rules locally
```

`nexora:deploy` puts the site in maintenance mode (production only), runs migrations, seeds platform data (`PlatformSeeder`, safe to run every time), creates the storage link, builds caches, restarts queue workers, brings the site back and runs `nexora:doctor`. It exits non-zero if anything fails, so a broken deploy is marked as failed.

The first time on a new server, also run `php artisan nexora:create-super-admin`.

## Tests

```bash
php artisan test
vendor/bin/pint --test
```

Tests run on SQLite in memory. Tenant isolation, access rules and payment idempotency must always have tests.

## Tenancy in one paragraph

`ResolveTenant` finds the teacher from the subdomain and stores it in `CurrentTeacher`. Tenant models use the `BelongsToTeacher` trait: their queries are limited to the current teacher and fail closed (`TenantNotResolved`) when there is none. Users are looked up through `TenantUserProvider`, so a session from one teacher's site can never log in on another. Cross-tenant code must say so explicitly with `Teacher::withoutTenancy(fn () => ...)`.
