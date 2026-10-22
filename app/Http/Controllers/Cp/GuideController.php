<?php

namespace App\Http\Controllers\Cp;

use App\Http\Controllers\Controller;
use Statamic\Facades\YAML;

/**
 * The editor guide in the CP: /cp/anleitung.
 *
 * Content in resources/redaktion/anleitung.yaml, screenshots in
 * resources/redaktion/bilder (taken by scripts/docs/screenshots.mjs). Both stay
 * outside public/, so only CP users can see them.
 */
class GuideController extends Controller
{
  protected const PATH = 'redaktion';

  public function show()
  {
    return view('redaktion.anleitung', [
      'guide' => YAML::file(resource_path(self::PATH.'/anleitung.yaml'))->parse(),
    ]);
  }

  public function image(string $file)
  {
    $path = resource_path(self::PATH.'/bilder/'.$file);

    abort_unless(is_file($path), 404);

    return response()->file($path, ['Cache-Control' => 'private, max-age=604800']);
  }
}
