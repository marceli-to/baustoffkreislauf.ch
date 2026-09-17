<p align="center"><img src="https://statamic.com/assets/branding/Statamic-Logo+Wordmark-Rad.svg" width="400" alt="Statamic Logo" /></p>

## About Statamic

Statamic is the flat-first, Laravel + Git powered CMS designed for building beautiful, easy to manage websites.

> **Note:** This repository contains the code for the Statamic application. To contribute to the core package, visit the [Statamic core package repository][cms-repo].


## Learning Statamic

Statamic has extensive [documentation][docs]. We dedicate a significant amount of time and energy every day to improving them, so if something is unclear, feel free to open issues for anything you find confusing or incomplete. We are happy to consider anything you feel will make the docs and CMS better.

## Support

We provide official developer support on [Statamic Pro](https://statamic.com/pricing) projects. Community-driven support is available on the [forum](https://statamic.com/forum) and in [Discord][discord].


## Contributing

Thank you for considering contributing to Statamic! We simply ask that you review the [contribution guide][contribution] before you open issues or send pull requests.


## Code of Conduct

In order to ensure that the Statamic community is welcoming to all and generally a rad place to belong, please review and abide by the [Code of Conduct](https://github.com/statamic/cms/wiki/Code-of-Conduct).


## Important Links

- [Statamic Main Site](https://statamic.com)
- [Statamic Documentation][docs]
- [Statamic Core Package Repo][cms-repo]
- [Statamic Migrator](https://github.com/statamic/migrator)
- [Statamic Discord][discord]

[docs]: https://statamic.dev/
[discord]: https://statamic.com/discord
[contribution]: https://github.com/statamic/cms/blob/master/CONTRIBUTING.md
[cms-repo]: https://github.com/statamic/cms

---

## Project notes: protected downloads (Blindside)

The "Blindside" pages (`baustofftage-*` in the `pages` collection) are hidden,
login-protected pages for the annual Baustofftage. They are not linked from any
navigation and carry `protect: logged_in`. Participants log in at `/login`, where
only a password is prompted for — the account is filled in for them.

The PDFs behind those pages live in `public/assets/protected/` and are served by
`App\Http\Controllers\Auth\DownloadController` via the `auth`-guarded route
`/download/{filename}`.

### One account per edition

Every Baustofftage edition has its own shared account, and each account only
opens the pages of its own edition. 2025 and 2026 used to share one account —
`blindside-baustofftage@…`, the oldest one — and therefore one password; they were
split in August 2026 by adding `blindside-2026@…` with the same password, so no
participant of either year had to be handed a new one. That account e-mail is the
only one without a year in it; it is the 2025 edition.

The wiring lives in **`config/blindside.php`**, which maps an account e-mail to the
page slugs it owns:

    'blindside-2027' => [
      'label' => 'Baustofftage 2027',
      'email' => 'blindside-2027@baustoffkreislauf.ch',
      'pages' => ['baustofftage-2027*'],
    ],

That config drives three things:

* **Which account the login form signs in to.** `/login` reads the `redirect`
  query parameter that `protect: logged_in` appends, looks up the edition of the
  target page and writes its e-mail into the hidden field
  (`{{ blindside:login_email }}`). Without a redirect the `default` edition is
  used. Nothing is hardcoded in the view any more.
* **Which pages an account may open.** The `logged_in` scheme in
  `config/statamic/protect.php` uses the custom `blindside` driver
  (`App\Auth\Protect\BlindsideProtector`, registered in `AppServiceProvider`).
  Signing in with the wrong edition's account does not produce a dead end: the
  visitor is logged out and handed back to the login form, which then asks for
  the password of the right edition.
* **Which PDFs an account may download.** `App\Support\Blindside` collects every
  `protected/…` path linked on the pages of each edition, and
  `DownloadController` only serves a file to an account of an edition that links
  it. Files in `protected/` that no edition page links to (e.g. the PFAS
  documents) stay readable for any logged-in account, as before.

Slugs matching `guarded` (`baustofftage-*`) **must** appear in one of the
editions. A protected page matching that pattern but missing from the config is
denied to everyone — deliberately, so a new edition cannot silently inherit the
previous year's audience. Super admins bypass all of it.

Adding next year's edition:

1. Create the user in the CP (`blindside-<year>@baustoffkreislauf.ch`).
2. Add a block to `config/blindside.php` and point `default` at it. The handle
   must not be purely numeric — PHP would turn it into an integer array key.
3. Deploy and run `php artisan config:clear`.

Content and users are **not** in Git (see `.gitignore`), so the pages and the
account only ever exist on the server; only the config travels with a deploy.

### Required server config — NOT in version control

Because the `assets` disk is rooted at `public_path('assets')`
(`config/filesystems.php`), Apache serves those files straight from the docroot,
bypassing the controller's auth check entirely. To prevent that, this file must
exist:

    public/assets/protected/.htaccess

with:

    Require all denied

`public/assets` is listed in `.gitignore`, so this file is **not tracked by Git**.
It has to be recreated by hand after restoring the assets directory from a backup
or setting up a new environment — otherwise every "protected" document is publicly
downloadable to anyone who knows or guesses a filename.

Blocking direct access does not affect the site: PDFs are linked through
`/download/{filename}` and images (e.g. the sponsor logos in
`protected/sponsoren/`) are rendered through Glide at `/img/asset/...`. Both read
from the filesystem via PHP and are unaffected by the Apache rule.

Verify with:

    curl -s -o /dev/null -w '%{http_code}\n' https://www.baustoffkreislauf.ch/assets/protected/<file>.pdf   # expect 403
    curl -s -o /dev/null -w '%{http_code}\n' https://www.baustoffkreislauf.ch/download/<file>.pdf            # expect 302 to /login

A durable fix would be to move `protected/` out of the docroot to
`storage/app/protected` and point a separate disk at it; that also requires moving
the Statamic asset container.

---

## Project notes: where form notifications go

Every public form sends two mails: a confirmation to the person who submitted it
and a notification to the responsible mailbox. Anlässe are handled by the
Eventbereich and its own mailbox, everything else by `info@`. Since `.env` is
gitignored, the wiring is documented here.

| Form | Controller | Notification to | Reply-To on both mails |
| --- | --- | --- | --- |
| Anlässe (`events`) | `Api\EventController` | `MAIL_TO_EVENTS` | `MAIL_REPLY_TO_ADDRESS_EVENTS` |
| Kurse (`courses`) | `Api\CourseController` | `MAIL_TO` | `MAIL_REPLY_TO_ADDRESS` |
| Publikations-Bestellungen | `Api\PublicationController` | `info@baustoffkreislauf.ch`, hardcoded | `MAIL_REPLY_TO_ADDRESS` |

The two `…_EVENTS` variables were added in September 2026, when the Eventbereich
got its own address (`events@baustoffkreislauf.ch`). Everything that concerns an
Anlass goes there: the notification about a new Anmeldung, and any reply a
participant sends to their Anmeldebestätigung. In practice both variables hold
the same address; they are separate only because the two they shadow are.

The rule is **append `_EVENTS` to the base variable**. Each one falls back to its
base when unset or empty, so an environment that defines neither behaves exactly
as before — which is what the local setup and any older environment do.

`MAIL_FROM_ADDRESS` is the sender for all of them and is not per-form; the mails
are sent from `no-reply@` regardless, which is why Reply-To matters.

Note that these are read with `env()` inside the controllers and notification
classes, not through `config/mail.php`. That works because the deploy (see
`INSTALL.txt`) clears caches but never runs `config:cache` — under a cached config
`env()` returns `null` and the mails would go to nobody. Move them into
`config/mail.php` before adding config caching.

One rough edge, unchanged by the above: the *owner* notification carries the same
Reply-To as the participant's confirmation. Whoever opens "Neue Anmeldung: …" and
hits Reply therefore writes to the mailbox they are already in, not to the person
who just registered. Setting that one mail's Reply-To to the registrant's address
would be more useful.
