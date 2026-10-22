# Editor guide

The guide for the editors lives in the CP at `/cp/anleitung` (sidebar: Hilfe ›
Anleitung) and is only visible to logged-in CP users.

- Content: `resources/redaktion/anleitung.yaml`
- Screenshots: `resources/redaktion/bilder` (`cp-…` from the CMS, `web-…` from the website)
- View: `resources/views/redaktion/`, styles and script in `resources/redaktion/`
- Route and nav item: `AppServiceProvider`, controller `App\Http\Controllers\Cp\GuideController`

## After changing an element

All steps run locally against Herd (`https://baustoffkreislauf.ch.test`).

1. Update the demo pages – one hidden page per element below
   `/doku-inhaltselemente`, built from real texts and assets:

       php scripts/docs/demo-content.php            # create / update
       php scripts/docs/demo-content.php --delete   # remove

2. Retake the screenshots. Needs a local super user with the CP language set to
   German (Voreinstellungen); the Statamic Pro trial alert is dismissed
   automatically.

       cd scripts/docs && npm install
       DOCS_EMAIL=… DOCS_PASSWORD=… node screenshots.mjs            # all, ~5 min
       DOCS_EMAIL=… DOCS_PASSWORD=… node screenshots.mjs cp-teaser  # single ones

3. Adjust the text in `anleitung.yaml` and commit the screenshots with it.
