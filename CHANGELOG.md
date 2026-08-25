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
