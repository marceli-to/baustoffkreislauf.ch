<?php

return [

  /*
  |--------------------------------------------------------------------------
  | Sites
  |--------------------------------------------------------------------------
  |
  | Each site should have root URL that is either relative or absolute. Sites
  | are typically used for localization (eg. English/French) but may also
  | be used for related content (eg. different franchise locations).
  |
  */

  'sites' => [
    'de' => [
      'name' => 'Deutsch',
      'locale' => 'de',
      'abbreviation' => 'd',
      'url' => env('APP_URL')
    ],
    'fr' => [
      'name' => 'Francais',
      'locale' => 'fr',
      'lang' => 'fr',
      'url' => env('APP_URL').'/fr/'
    ],
    'it' => [
      'name' => 'Italiano',
      'locale' => 'it',
      'lang' => 'it',
      'url' => env('APP_URL').'/it/'
    ],
  ],
];
