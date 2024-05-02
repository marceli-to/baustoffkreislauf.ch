<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Symfony\Component\Yaml\Yaml;

class NewsletterController extends Controller
{
    public function confirm($hash)
    {
      // Get all files in storage/forms/newsletter
      $files = \File::files(storage_path('forms/newsletter'));

      // find the markdown file with the hash: $hash
      // loop through all files
      foreach ($files as $file)
      {
        $content = \File::get($file);
        $yaml = Yaml::parse($content);

        if ($yaml['hash'] === $hash)
        {
          $yaml['confirmed'] = true;
          \File::put($file, Yaml::dump($yaml));

          $redirects = [
            'de' => '/newsletter/anmeldung-bestaetigt',
            'fr' => '/fr/newsletter/inscription-confirmee',
            'it' => '/it/newsletter/iscrizione-confermata',
          ];

          return redirect($redirects[$yaml['language']]);
        }
      }
    }
}