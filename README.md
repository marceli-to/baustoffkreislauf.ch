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
the account e-mail is hardcoded in `resources/views/auth/login.antlers.html`, so
only a password is prompted for.

The PDFs behind those pages live in `public/assets/protected/` and are served by
`App\Http\Controllers\Auth\DownloadController` via the `auth`-guarded route
`/download/{filename}`.

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
