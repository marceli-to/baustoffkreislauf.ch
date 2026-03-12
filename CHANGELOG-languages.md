# Publications: Languages Field

## Blueprint Change

A new field `languages` has been added to the Publications blueprint (`resources/blueprints/collections/publications/publication.yaml`).

### Field Details

- **Handle:** `languages`
- **Type:** Checkboxes
- **Display:** Verfügbare Sprachen
- **Options:** de (Deutsch), fr (Français), it (Italiano)
- **Localizable:** No
- **Position:** Between "Kosten/Preis" and "Dateien"

### How It Works

In the Statamic control panel, editors can select which languages a publication is available in. Multiple selections are possible.

### Impact on Order Form

The order form dynamically adapts based on the selected languages:

- If languages are set on a publication, the form shows a separate quantity field per language (e.g. "Anzahl (Deutsch)", "Anzahl (Français)")
- If no languages are set, the form falls back to a single "Anzahl" field
- At least one language must have a quantity greater than 0 for the order to be valid

### API Changes

- **GET `/api/publication/{id}`** — now returns a `languages` field
- **POST `/api/publication/order`** — accepts a `quantities` object (e.g. `{"de": 2, "fr": 1}`) instead of a single `quantity` + `language`
- Backend validation ensures `quantities` is an array of integers >= 0 with at least one value > 0
