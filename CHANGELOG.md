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
