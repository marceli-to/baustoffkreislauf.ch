# Statamic 6 upgrade – progress

Working notes for the upgrade and cleanup. Start a new session by reading this
file, then `CHANGELOG.md` (entry 22.10.2026, which holds the deploy steps).
Delete this file once everything below is done.

Last updated: 23.10.2026

## State

Branch `statamic-6`, not pushed, not deployed. Statamic 6.36, Laravel 13.38,
PHP 8.3+ (local 8.4).

| Phase | Status |
|---|---|
| 0 Baseline HTML snapshot of all URLs | done |
| 1 Laravel 10 → 12 | done |
| 2 Statamic 5 → 6 | done |
| 2b Laravel 12 → 13 | done |
| 3 Content model cleanup (26 → 13 page sets) | done |
| 4 Templates: one partial per set in `partials/sets/` | done |
| CP: Eingänge section, wider sidebar, Pages → Seiten | done |
| Unused assets: images only, documents stay | decided 23.10. |
| Unused images: Excel list for the client | to send / waiting |
| Unused images: delete command | done (`assets:unused --delete`) |
| Unused images: delete on the server | **open** |
| Image upload size | **open – decision** |
| Deploy to production | **open** |
| Optimisation pass (static caching, Tailwind 4 / Vite 7) | **open – later** |

Local content is a fresh `statamic:pull` from production (22.10.2026) with the
upgrade steps already applied (updates:run once, `content:migrate-sets`,
collection renamed to Seiten). All 467 URLs render.

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

- `php please updates:run 5.73.23` must run **exactly once** per content copy.
  A second run sets the FR/IT globals' `sites` origin to `null`, so they stop
  inheriting from DE.
- Every new `statamic:pull` before the deploy brings Statamic 5 content back:
  repeat deploy steps 3–6 from the CHANGELOG locally.
- `content/`, `users/`, `public/assets` are gitignored and live on the server.
- The first `Entry::all()` after a stache refresh needs more than 128 MB:
  use `php -d memory_limit=1G artisan …`.
- Statamic 5's French translations covered «Downloads» → added to `lang/fr.json`.

## Open items

1. **Unused images.** Only images get deleted; PDFs and other documents stay
   (often linked from newsletters and mails). Client marks
   `~/Downloads/baustoffkreislauf-unbenutzte-bilder.xlsx` (121 images, 197 MB,
   from the 22.10. pull). When it comes back, on the **server**:
   - write the paths of the rows with «Löschen? = Ja» to a file, one per line
     (folder/file, root files without folder)
   - optionally check the access logs for these paths first (hotlinks from
     newsletters)
   - `php artisan assets:unused --only=paths.txt` to see the list, then
     `php artisan assets:unused --only=paths.txt --delete --backup=~/unused-images-<date>`.
     It skips documents, re-checks each file right before deleting and copies it
     to the backup first. If files were added outside the CP, run
     `php artisan cache:clear` first so Statamic sees them.
2. **Image upload size.** `source_preset: max_upload_size` already caps uploads
   at 4000px; the site never renders above 2000px. 158 images are 2600–4000px
   (346 MB). Lowering the cap to ~2560px would also shrink the hi-res press
   photos (`image_downloads.hires_image`) – needs a decision, possibly a separate
   container for those.
3. **Content fix for the editors.** The EPD post (`4d4f07fe-…`) has two FR and two
   IT localizations with the same origin; delete one of each in the CP.
4. **Deploy.** Follow the CHANGELOG steps; production PHP to 8.4/8.5 first.
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
