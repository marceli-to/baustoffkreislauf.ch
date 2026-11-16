# Statamic 6 upgrade – progress

Working notes for the upgrade and cleanup. Start a new session by reading this
file, then `CHANGELOG.md` (entry 22.10.2026, which holds the deploy steps).
Delete this file once everything below is done.

Last updated: 16.11.2026

## State

Merged into `master` and deployed to production on 16.11.2026, then updated the
same day to Statamic 6.42 / Laravel 13.42. PHP 8.4 on the server and locally.

| Phase | Status |
|---|---|
| 0 Baseline HTML snapshot of all URLs | done |
| 1 Laravel 10 → 12 | done |
| 2 Statamic 5 → 6 | done |
| 2b Laravel 12 → 13 | done |
| 3 Content model cleanup (26 → 13 page sets) | done |
| 4 Templates: one partial per set in `partials/sets/` | done |
| CP: Eingänge section, wider sidebar, Pages → Seiten | done |
| Unused assets: delete all unused images (backup), documents stay | decided 23.10. |
| Unused images: delete command | done (`assets:unused --delete`) |
| Unused images: delete on the server | done 16.11. (backup `~/unused-images-2026-11-16`, 138 files) |
| Image upload size | decided 16.11.: stays at 4000px |
| Deploy to production | done 16.11. |
| Optimisation pass (static caching, Tailwind 4 / Vite 7) | **open – later** |

Production content is migrated, so a `statamic:pull` now brings Statamic 6
content: the upgrade steps are never needed again.

## What changed (details in the commits)

- Removed: Sanctum, Solo, `rias/statamic-collection-groups`. Submissions
  (event/course registrations, orders) are an «Eingänge» CP section via
  `Nav::extend` in `AppServiceProvider`; `resources/preferences.yaml` orders it
  below Content.
- Page sets: `teasers` (manual / post / entry from Teaser collection), `tiles`
  (was teaser_services + teaser_topics), `module` (events, courses, course_form,
  order_form, publications, search), `images` (was image + images, an item with
  «Ganze Breite» renders 16:9), Bard `download` (was press_release + logo).
- `app/Console/Commands/MigrateContentModel.php` = `content:migrate-sets`,
  idempotent, `--dry-run`.
- `app/Console/Commands/UnusedAssets.php` = `assets:unused --csv=…`, report only.
- `resources/css/cp.css` widens the CP sidebar to 15rem (overrides core values –
  check after Statamic updates).
- Fixed on the way: course form rendered twice, logo downloads had `src="/"`.

## Gotchas

- Never run `php please updates:run 5.73.23` again – production and pulled
  content are already converted; a second run sets the FR/IT globals' `sites`
  origin to `null`, so they stop inheriting from DE.
- Server: web PHP is set by the `Use php-fpm phpXX` line in `public/.htaccess`
  (see item 4); CLI is `/usr/local/php84/bin/php`, plain `php` is 8.3; `sed` is
  BSD (`sed -i ""`).
- Statamic minor updates: `composer update` locally, compare snapshots, deploy
  with `composer install`; never update from the CP.
- `content/`, `users/`, `public/assets` are gitignored and live on the server.
- The first `Entry::all()` after a stache refresh needs more than 128 MB:
  use `php -d memory_limit=1G artisan …`.
- Statamic 5's French translations covered «Downloads» → added to `lang/fr.json`.

## Open items

1. **Unused images.** Done 16.11.2026: 138 images (231 MB) deleted, backup in
   `~/unused-images-2026-11-16` on the server; delete it from ~mid-December if
   nobody missed anything (restore = copy back into `public/assets/`, keeping
   the subfolder). `assets:unused --backup=~/…` now expands `~` itself – the
   shell does not after `=`, and the first run created a folder named `~`.
2. **Image upload size.** Decided 16.11.2026: stays at 4000px
   (`source_preset: max_upload_size`), no separate container for press photos.
3. **EPD post duplicates.** Done 16.11.2026: the older FR/IT copies
   (`nouveau-…`, `nuovo-…`) deleted in the CP; the newer ones remain.
4. **Deploy.** Done 16.11.2026. Gotcha: the server's `public/.htaccess` (not in
   git) pins the web PHP with its own `Use php-fpm phpXX` line below the
   Hostpoint panel block; it said php82 and caused a 500. Now php84 – change it
   on every PHP bump.
5. **Optimisation (later, separate branch).** Static caching is off
   (`STATAMIC_STATIC_CACHING_STRATEGY=null`) – biggest win. Then Tailwind 3 → 4,
   Vite 4 → 7, laravel-vite-plugin.

## Verifying changes

`scripts/upgrade/snapshot.sh` fetches every URL from Herd and normalises the
volatile parts; `scripts/upgrade/compare.sh` diffs two snapshots ignoring
whitespace. Take one before and one after a change:

    scripts/upgrade/snapshot.sh /tmp/before
    # … change …
    URLS=/tmp/before/_urls.txt scripts/upgrade/snapshot.sh /tmp/after
    SHOW=40 scripts/upgrade/compare.sh /tmp/before /tmp/after
