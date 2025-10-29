# Repository Guidelines
## Project Structure & Module Organization
- `app/` contains Laravel 12 application code (HTTP controllers, jobs, policies) grouped by domain; share utilities via `app/Helpers`.
- `routes/web.php` handles browser flows, `routes/api.php` exposes JSON endpoints, and `routes/channels.php` registers broadcasting.
- `database/migrations` and `database/seeders` manage schema; factories live in `database/factories` for curated test data.
- Front-end sources live in `resources/js` (ES modules) and `resources/css`; Blade views sit under `resources/views` alongside partials.
- Compiled assets publish to `public/`; configurable defaults stay in `config/`, while feature docs and deployment scripts live in `docs/` and `deployment/`.

## Build, Test, and Development Commands
- `composer install` and `npm install` align PHP and JS dependencies after cloning or switching branches.
- `php artisan migrate --seed` prepares the database (tests use sqlite memory, local dev targets `.env` connection).
- `composer run dev` boots the full stack: HTTP server, queue listener, log tail, and Vite dev server.
- Use `php artisan serve` + `npm run dev` for lighter workflows; package production assets with `npm run build`.
- Backend tests run via `php artisan test` (add `--filter FooTest` to focus); format PHP before commits using `./vendor/bin/pint`.

## Coding Style & Naming Conventions
- `.editorconfig` enforces UTF-8, LF endings, and four-space indentation; keep Markdown trailing spaces intact.
- PHP follows PSR-12; classes are StudlyCase, controllers end with `Controller`, Eloquent models map 1:1 with tables, and migrations/seeders use snake_case filenames.
- Blade partials use snake_case and live beside their feature views; JavaScript modules export ES modules with camelCase functions and PascalCase singletons.
- Run Prettier on front-end assets (`npx prettier --check "resources/**/*.{js,css}"`) and keep SCSS/utility files grouped by feature folder.

## Testing Guidelines
- PHPUnit drives the test suite (`tests/Feature`, `tests/Unit`); stick to the `test_*` method naming already in place.
- Include `RefreshDatabase` or database transactions for stateful tests and rely on factories for fixtures.
- Record new behaviours with feature tests and guard business rules in unit tests; ensure `php artisan test` passes before pushing.

## Commit & Pull Request Guidelines
- Write imperative, scoped commit subjects ("Fix deployment variable escaping"), mirroring existing history and keeping bodies focused on rationale.
- Each PR should link related issues, flag schema or config changes, attach UI screenshots when relevant, and list the commands/tests you ran.
- Request early review for updates touching `deployment/` automation or shared configs, and confirm CI/CD secrets are unaffected.

## Environment & Configuration Notes
- Copy `.env.example` when onboarding; never commit `.env`.
- Queue processing and logs run via the dev script; mirror that setup in production using Supervisor or systemd.
- Keep third-party keys in the secrets manager and review Spatie permission roles before seeding production data.
