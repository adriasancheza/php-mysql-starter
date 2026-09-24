# php-mysql-starter

[![CI](https://github.com/adriasancheza/php-mysql-starter/actions/workflows/ci.yml/badge.svg)](https://github.com/adriasancheza/php-mysql-starter/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-8.3-777bb4?logo=php&logoColor=white)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

A minimal, dependency-light **PHP 8.3 + MySQL** starter built for cheap
shared hosting: plain Apache, FTP deploy, no root/SSH access, no framework
lock-in. No Laravel, no Symfony — just a small, readable core you can
actually read in one sitting.

## Why

Most PHP starters assume you have Docker, a VPS, and Composer plugins that
need shell access to install. This one assumes the opposite: a shared
hosting control panel, an FTP client, and `composer install` run once on a
machine you actually control (then upload `vendor/`). Everything else —
routing, sessions, CSRF, auth, migrations — is a few hundred lines of plain
PHP you can read end to end.

## Features

- **Front controller** (`public/index.php`) with `.htaccess` rewrite;
  `public/` is the only web-exposed directory.
- **PSR-4 autoloading** (`App\`) via Composer, no framework.
- **Tiny router** — GET/POST/PUT/PATCH/DELETE, `{param}` route segments.
- **Request/Response** helpers and plain PHP view templates with an `e()`
  escaping helper (no template engine dependency).
- **Config from `.env`** via a ~50-line loader (no `vlucas/phpdotenv`).
- **PDO wrapper**: exceptions on error, prepared statements only, utf8mb4.
- **Migrations runner** (`bin/migrate.php`) — plain `.sql` files, tracked in
  a `migrations` table, safe to re-run.
- **Auth**: register/login/logout with `password_hash`/`password_verify`,
  hardened sessions (httponly, SameSite=Lax, id regeneration), CSRF token
  middleware on every unsafe request, DB-backed login rate limiting, flash
  messages.
- **Example CRUD module**: notes owned by the logged-in user, with
  per-user query scoping (no "guess another user's ID" bugs).
- **Tests**: PHPUnit 11 — unit tests for the router, CSRF, env loader, and
  validator; integration tests for auth + notes against real MySQL, which
  **skip automatically** when no database is configured.
- **Static analysis**: PHPStan level 6.
- **CI**: GitHub Actions running PHP 8.3 + `mysql:8`, migrations, PHPUnit,
  and PHPStan on every push/PR.

## Requirements

- PHP >= 8.3 with `pdo_mysql`, `mbstring`
- MySQL 5.7+/8.0 (or MariaDB 10.x)
- Composer (only needed locally / in CI — see deploy notes below)
- Apache with `mod_rewrite` (typical on shared hosting)

## Quick start (local)

```bash
git clone https://github.com/adriasancheza/php-mysql-starter.git
cd php-mysql-starter
composer install
cp .env.example .env        # edit DB_* credentials
php bin/migrate.php
php -S localhost:8000 -t public
```

Visit `http://localhost:8000`, register an account, and start creating
notes.

Run the test suite:

```bash
composer test    # PHPUnit; integration tests need a MySQL connection
composer stan     # PHPStan level 6
```

## Deploying to shared hosting (FTP)

1. On a machine with Composer, run `composer install --no-dev --optimize-autoloader`
   (or without `--no-dev` if you want to debug on the server).
2. Upload the whole project **except** `.env`, `.git/`, and anything in
   `.gitignore`, via FTP/SFTP.
3. Point the hosting account's **document root** at `public/`. Most panels
   (cPanel, Plesk, Arsys, etc.) let you set this per-domain; if yours
   doesn't, the root-level `.htaccess` included here redirects requests
   into `public/` as a fallback.
4. Create the MySQL database and user from your hosting panel, then copy
   `.env.example` to `.env` on the server (via FTP or the panel's file
   manager) and fill in the real credentials. **Never commit `.env`.**
5. Run migrations once, either via SSH if available
   (`php bin/migrate.php`) or by temporarily exposing a protected
   migration endpoint — most shared hosts without SSH will require you to
   run the migration SQL manually via phpMyAdmin instead.
6. Verify `public/.htaccess` was uploaded (some FTP clients hide
   dotfiles by default) and that `mod_rewrite` is enabled.

## Project structure

```
php-mysql-starter/
├── bin/
│   └── migrate.php          # migration runner CLI
├── migrations/               # plain .sql migration files
├── public/                   # web root — the ONLY exposed directory
│   ├── .htaccess
│   └── index.php             # front controller
├── src/
│   ├── Auth/                 # User, UserRepository, AuthService,
│   │                         # RateLimiter, AuthController
│   ├── Core/                 # Router, Request, Response, View, Session,
│   │   └── Middleware/       # Csrf, Database, and CSRF/Auth middleware
│   ├── Notes/                # example CRUD module (owned by user)
│   ├── Support/              # Env loader, Validator
│   └── bootstrap.php
├── tests/
│   ├── Unit/                 # Router, Csrf, Env, Validator
│   └── Integration/          # Auth + Notes against real MySQL
├── views/                    # plain PHP templates
├── .env.example
├── composer.json
├── phpstan.neon.dist
├── phpunit.xml.dist
└── .github/workflows/ci.yml
```

## Security notes

- Passwords are hashed with `password_hash()` (bcrypt/argon2i depending on
  PHP build) and verified with `password_verify()` — never compared or
  stored in plain text.
- Every state-changing request must carry a valid, session-bound CSRF
  token (`App\Core\Middleware\CsrfMiddleware`).
- Sessions are `httponly`, `SameSite=Lax`, and the session id is
  regenerated on login/logout to mitigate fixation.
- Login attempts are throttled per email + IP via a `login_attempts`
  table (`App\Auth\RateLimiter`).
- All SQL goes through PDO prepared statements — no string-concatenated
  queries anywhere in the codebase.
- Notes queries are always scoped by `user_id`, so one user can never
  read, edit, or delete another user's data even by guessing an id.
- `.env` is gitignored; only `.env.example` (no real secrets) is
  committed.
- `public/` is the only directory meant to be web-exposed; `src/`,
  `migrations/`, `bin/`, and `vendor/` should never be reachable directly
  (enforced by the included `.htaccess` files as a defense-in-depth
  measure — always also configure the real document root when possible).

## Testing

- `tests/Unit` — pure logic, no I/O: router matching, CSRF token
  generation/verification, `.env` parsing, validator rules.
- `tests/Integration` — exercise `AuthService` and `NoteRepository`
  against a real MySQL database (applying migrations automatically). If no
  `DB_*` environment is reachable, these tests report as **skipped**
  rather than failing, so `composer test` is safe to run without MySQL
  installed.

## License

MIT © 2026 [Adrià Sánchez](https://github.com/adriasancheza) — see
[LICENSE](LICENSE).
