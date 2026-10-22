## 22.10.2026 – Statamic 6, Laravel 13, aufgeräumte Seitenelemente
- Statamic 5 → 6, Laravel 10 → 13, **PHP 8.3+ nötig** (8.4/8.5 empfohlen). Sanctum, Solo und `rias/statamic-collection-groups` sind raus; Anmeldungen und Bestellungen stehen im CP jetzt in einer eigenen Rubrik «Eingänge»
- Seitenelemente von 26 auf 13 reduziert:
  - «Teaser (Collection)» ist jetzt «Teaser aus Sammlung» im Element «Teaser»
  - «Teaser Unterseiten» und «Teaser Themen» sind jetzt «Kacheln»
  - Anlässe, Teaser Kurse, Kursanmeldung, Bestellformular, Publikationen und Suche sind jetzt «Modul» mit Auswahl
  - «Einzelbild» und «Mehrere Bilder» sind jetzt «Bilder»
  - Im Texteditor sind «Medienmitteilung» und «Logo Download» jetzt «Download»
- Behoben: Kursanmeldung wurde doppelt angezeigt, Logo-Downloads hatten kein Bild-`src`
- Entwickler-Einstellungen (Template, Layout, Debug, Seitenklasse) sind für Redakteure ausgeblendet
- Neu: `php artisan assets:unused --csv=unused.csv` listet Dateien, auf die nichts verweist (löscht nichts)
- CP-Seitenleiste auf 15rem verbreitert (`resources/css/cp.css`), damit die deutschen Bezeichnungen auf eine Zeile passen
- Deploy:
  1. `content/` auf dem Server sichern, Redaktion für ein paar Minuten nichts speichern lassen
  2. PHP-Version der Site auf 8.4 (oder 8.5) stellen, dann `composer install --no-dev` wie gewohnt
  3. `php please updates:run 5.73.23` – stellt die Globals auf das Format von Statamic 6 um (lokal schon gelaufen, auf dem Server nicht, weil dort kein `composer update` läuft). **Nur einmal ausführen**: ein zweiter Lauf setzt die Herkunft der FR/IT-Globals auf `null`, dann erben sie nicht mehr von DE
  4. `php artisan content:migrate-sets` – überführt die Inhalte auf die neuen Elemente; vorher mit `--dry-run` prüfen (lokal: 51 Einträge)
  5. `php artisan config:clear`, `php please stache:refresh`, `php artisan cache:clear`
  6. Im CP die Collection «Pages» in «Seiten» umbenennen (oder `title: Seiten` in `content/collections/pages.yaml`) – liegt im Content und kommt deshalb nicht mit dem Deploy

## 25.08.2026 – Baustofftage 2025 und 2026 getrennt
- Die beiden Jahrgaenge hingen seit je am selben Konto `blindside-baustofftage@baustoffkreislauf.ch` (und damit am selben Passwort); der Login zeigte sie deshalb als "Baustofftage 2025 / 2026"
- Neuer User `blindside-2026@baustoffkreislauf.ch` im CP – **mit demselben Passwort wie das bestehende Konto**, damit niemand ein neues Passwort braucht. Auf dem Server am einfachsten so: `users/blindside-baustofftage@baustoffkreislauf.ch.yaml` nach `users/blindside-2026@baustoffkreislauf.ch.yaml` kopieren, darin `name` auf 'Blindside Baustofftage 2026' und `id` auf eine neue UUID setzen (`password_hash` unveraendert lassen)
- `blindside-baustofftage@…` bleibt das Konto fuer 2025
- `config/blindside.php`: je ein Block fuer 2025, 2026 und 2027; der Login zeigt jetzt den einzelnen Jahrgang und jedes Konto kommt nur noch an die PDFs seines Jahrgangs
- Ab jetzt laesst sich das Passwort eines Jahrgangs aendern, ohne den anderen zu treffen
- Nach dem Deploy `php artisan config:clear` und `php please stache:refresh` ausfuehren (sonst kennt Statamic den neuen User nicht)

## 25.08.2026 – Eigener Login pro Baustofftage-Edition
- Neuer User `blindside-2027@baustoffkreislauf.ch` im CP (nur auf dem Server)
- `config/blindside.php`: Editionen (Konto ↔ Seiten) pflegen, `default` auf die aktuelle Edition setzen
- `/login` wählt das Konto anhand des `redirect`-Ziels, es wird weiterhin nur ein Passwort abgefragt
- Nach dem Deploy `php artisan config:clear` ausführen

## 27.06.2025 – Bestellformular für Publikationen
- Set link in /views/partials/publication/item.antlers.html
- Copy collection "orders" to server
- Create Page "Bestellung Arbeitshilfen & Publikationen" as child page of "Arbeitshilfen & Publikationen"
- Create Page "Commande outils de travail et publications" as child page of "Arbeitshilfen & Publikationen"
- Create Page "Ordine Ausili & Pubblicazioni" as child page of "Arbeitshilfen & Publikationen"
- Integrate "Bestellformular Publikationen"
