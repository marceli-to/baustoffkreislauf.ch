<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Statamic\Facades\AssetContainer;

/**
 * Lists assets that no content, global, navigation or template refers to.
 * Only reports – deleting stays a decision for whoever reads the list, also
 * because files linked from newsletters or mails look unused from here.
 */
class UnusedAssets extends Command
{
  protected $signature = 'assets:unused {--container=assets} {--csv= : Write the list to this file}';

  protected $description = 'List assets that are not referenced anywhere';

  public function handle()
  {
    $haystack = $this->haystack();
    $unused = collect();

    foreach (AssetContainer::find($this->option('container'))->assets() as $asset) {
      $path = $asset->path();

      if (str_starts_with(basename($path), '.')) {
        continue;
      }

      // Bard links and asset fields store the plain path, hand-written HTML often the encoded one
      if (!str_contains($haystack, $path) && !str_contains($haystack, implode('/', array_map('rawurlencode', explode('/', $path))))) {
        $unused->push([$path, $asset->size(), $asset->lastModified()->format('Y-m-d')]);
      }
    }

    $unused = $unused->sortByDesc(1)->values();

    $this->table(['Pfad', 'MB', 'Geändert'], $unused->map(fn ($row) => [$row[0], round($row[1] / 1048576, 2), $row[2]]));
    $this->info($unused->count().' unused assets, '.round($unused->sum(1) / 1048576, 1).' MB');

    if ($csv = $this->option('csv')) {
      $handle = fopen($csv, 'w');
      fputcsv($handle, ['path', 'bytes', 'modified']);
      $unused->each(fn ($row) => fputcsv($handle, $row));
      fclose($handle);
      $this->info("Written to {$csv}");
    }

    return 0;
  }

  protected function haystack(): string
  {
    $dirs = [base_path('content'), base_path('users'), resource_path('views'), resource_path('blueprints'), resource_path('fieldsets'), config_path()];

    return collect($dirs)
      ->filter(fn ($dir) => File::isDirectory($dir))
      ->flatMap(fn ($dir) => File::allFiles($dir))
      ->reject(fn ($file) => str_contains($file->getPathname(), '/content/assets/'))
      ->map(fn ($file) => File::get($file->getPathname()))
      ->implode("\n");
  }
}
