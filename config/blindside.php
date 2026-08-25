<?php

return [

  /*
  |--------------------------------------------------------------------------
  | Editionen
  |--------------------------------------------------------------------------
  |
  | Die "Blindside"-Seiten der Baustofftage sind pro Jahrgang durch ein
  | eigenes Konto geschuetzt. Der Eintrag hier verbindet das Konto mit den
  | Seiten, die es oeffnen darf – und damit auch mit den PDFs, die auf
  | diesen Seiten verlinkt sind.
  |
  | Neue Edition aufschalten:
  | 1. User im CP erfassen (E-Mail nach dem Muster blindside-JAHR@…)
  | 2. Hier einen Block ergaenzen und 'default' auf den neuen Handle setzen
  |    (der Handle darf nicht rein numerisch sein – PHP macht daraus sonst
  |    einen Integer-Array-Key)
  | 3. Deployen und `php artisan config:clear` ausfuehren
  |
  */

  'editions' => [

    'blindside-2025' => [
      'label' => 'Baustofftage 2025',
      // Das aelteste Konto, deshalb ohne Jahrgang in der Adresse. Es behaelt
      // das Passwort, das den Teilnehmenden von 2025 kommuniziert wurde.
      'email' => 'blindside-baustofftage@baustoffkreislauf.ch',
      'pages' => ['baustofftage-2025*'],
    ],

    'blindside-2026' => [
      'label' => 'Baustofftage 2026',
      'email' => 'blindside-2026@baustoffkreislauf.ch',
      'pages' => ['baustofftage-2026*'],
    ],

    'blindside-2027' => [
      'label' => 'Baustofftage 2027',
      'email' => 'blindside-2027@baustoffkreislauf.ch',
      'pages' => ['baustofftage-2027*'],
    ],

  ],

  /*
  | Edition, die auf /login vorausgewaehlt wird, wenn kein (oder ein
  | unbekanntes) redirect-Ziel mitgegeben wird. Normalerweise die aktuelle.
  */

  'default' => 'blindside-2027',

  /*
  | Seiten, die zwingend zu einer der Editionen oben gehoeren muessen.
  | Eine geschuetzte Seite, die auf dieses Muster passt, aber in keiner
  | Edition steht, ist fuer niemanden zugaenglich (ausser Super-Admins).
  | Geschuetzte Seiten ausserhalb des Musters bleiben wie bisher fuer jedes
  | angemeldete Konto offen.
  */

  'guarded' => 'baustofftage-*',

];
