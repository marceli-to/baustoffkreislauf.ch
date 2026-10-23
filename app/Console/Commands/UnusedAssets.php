<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Statamic\Facades\AssetContainer;

/**
 * Lists assets that no content, global, navigation or template refers to.
 *
 * Reports by default. With --delete it removes unused images (never documents:
 * PDFs and the like are often linked from newsletters and mails, which look
 * unused from here). Every file is checked again right before it goes, so run
 * it on the server, against the live content.
 */
class UnusedAssets extends Command
{
  protected $signature = 'assets:unused
    {--container=assets}
    {--csv= : Write the list to this file}
    {--images : Only images (jpg, png, gif, webp, avif)}
    {--only= : Only the paths listed in this file, one per line}
    {--delete : Delete the unused images instead of listing them}
    {--backup= : Copy each image into this directory before deleting it}';

  protected $description = 'List assets that are not referenced anywhere, or delete unused images';

  public function handle()
  {
    if ($this->option('delete') && !$this->option('backup')) {
      $this->error('--delete needs --backup=<directory>: deleted files cannot be restored otherwise.');

      return 1;
    }

    $unused = $this->unused($this->option('delete') || $this->option('images'));

    $this->table(['Pfad', 'MB', 'Geändert'], $unused->map(fn ($row) => [$row[0], round($row[1] / 1048576, 2), $row[2]]));
    $this->info($unused->count().' unused assets, '.round($unused->sum(1) / 1048576, 1).' MB');

    if ($csv = $this->option('csv')) {
      $handle = fopen($csv, 'w');
      fputcsv($handle, ['path', 'bytes', 'modified']);
      $unused->each(fn ($row) => fputcsv($handle, $row));
      fclose($handle);
      $this->info("Written to {$csv}");
    }

    if ($this->option('delete') && $unused->isNotEmpty()) {
      return $this->delete($unused);
    }

    return 0;
  }

  protected function unused(bool $imagesOnly)
  {
    $haystack = $this->haystack();
    $only = $this->option('only') ? collect(file($this->option('only'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES))->map(fn ($line) => trim($line)) : null;
    $unused = collect();

    foreach (AssetContainer::find($this->option('container'))->assets() as $asset) {
      $path = $asset->path();

      if (str_starts_with(basename($path), '.') || ($imagesOnly && !$asset->isImage()) || ($only && !$only->contains($path))) {
        continue;
      }

      if (!$this->isReferenced($haystack, $path)) {
        $unused->push([$path, $asset->size(), $asset->lastModified()->format('Y-m-d'), $asset]);
      }
    }

    return $unused->sortByDesc(1)->values();
  }

  protected function delete($unused)
  {
    $backup = rtrim($this->option('backup'), '/');

    if (!$this->confirm("Delete these {$unused->count()} images? Copies go to {$backup}.")) {
      return 1;
    }

    // Content may have changed while the list was on screen
    $haystack = $this->haystack();
    $deleted = 0;

    foreach ($unused as [$path, $bytes, $modified, $asset]) {
      if ($this->isReferenced($haystack, $path)) {
        $this->warn("Skipped, now in use: {$path}");
        continue;
      }

      $target = "{$backup}/{$path}";
      File::ensureDirectoryExists(dirname($target));

      if (!File::put($target, $asset->disk()->get($path))) {
        $this->error("Backup failed, kept: {$path}");
        continue;
      }

      $asset->delete();
      $deleted++;
    }

    $this->info("{$deleted} images deleted, copies in {$backup}");

    return 0;
  }

  // Bard links and asset fields store the plain path, hand-written HTML often the encoded one
  protected function isReferenced(string $haystack, string $path): bool
  {
    return str_contains($haystack, $path)
      || str_contains($haystack, implode('/', array_map('rawurlencode', explode('/', $path))));
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
