<?php
namespace App\Support;

use Illuminate\Support\Str;
use Statamic\Facades\Entry;

class Blindside
{
  /**
   * Cache of "edition handle => list of protected file names", per request.
   */
  protected static ?array $files = null;

  public static function editions(): array
  {
    return config('blindside.editions', []);
  }

  public static function edition(?string $handle): ?array
  {
    return $handle ? (static::editions()[$handle] ?? null) : null;
  }

  /**
   * Does this slug have to belong to one of the configured editions?
   */
  public static function isGuarded(?string $slug): bool
  {
    $pattern = config('blindside.guarded');

    return $slug && $pattern && Str::is($pattern, $slug);
  }

  public static function editionForSlug(?string $slug): ?string
  {
    if (!$slug) {
      return null;
    }

    foreach (static::editions() as $handle => $edition) {
      if (Str::is($edition['pages'] ?? [], $slug)) {
        return (string) $handle;
      }
    }

    return null;
  }

  public static function editionForUrl(?string $url): ?string
  {
    return static::editionForSlug(static::slugFromUrl($url));
  }

  public static function editionForUser($user): ?string
  {
    if (!$user) {
      return null;
    }

    $email = Str::lower((string) $user->email());

    foreach (static::editions() as $handle => $edition) {
      if (Str::lower($edition['email'] ?? '') === $email) {
        return (string) $handle;
      }
    }

    return null;
  }

  /**
   * The edition the login form should sign in to, based on where the visitor
   * was headed. Falls back to the current edition.
   */
  public static function editionForLogin(?string $redirect): ?string
  {
    return static::editionForUrl($redirect) ?? config('blindside.default');
  }

  public static function loginEmail(?string $redirect): ?string
  {
    return static::edition(static::editionForLogin($redirect))['email'] ?? null;
  }

  public static function loginLabel(?string $redirect): ?string
  {
    return static::edition(static::editionForLogin($redirect))['label'] ?? null;
  }

  public static function slugFromUrl(?string $url): ?string
  {
    $path = trim((string) parse_url((string) $url, PHP_URL_PATH), '/');

    return $path === '' ? null : basename($path);
  }

  /**
   * May this user open the page with the given slug?
   */
  public static function mayOpen($user, ?string $slug): bool
  {
    if (!$user) {
      return false;
    }

    if ($user->isSuper()) {
      return true;
    }

    if (!static::isGuarded($slug)) {
      return true;
    }

    $edition = static::editionForSlug($slug);

    return $edition !== null && $edition === static::editionForUser($user);
  }

  /**
   * May this user download the given file from the protected folder?
   * Files that are not linked on any edition page keep the old rule: every
   * logged in account may fetch them.
   */
  public static function mayDownload($user, ?string $filename): bool
  {
    if (!$user) {
      return false;
    }

    if ($user->isSuper()) {
      return true;
    }

    $owners = static::editionsForFile($filename);

    if (empty($owners)) {
      return true;
    }

    return in_array(static::editionForUser($user), $owners, true);
  }

  /**
   * The editions whose pages link to the given file.
   */
  public static function editionsForFile(?string $filename): array
  {
    $filename = Str::lower(basename((string) $filename));

    return collect(static::files())
      ->filter(fn ($files) => in_array($filename, $files, true))
      ->keys()
      ->map(fn ($handle) => (string) $handle)
      ->all();
  }

  /**
   * All protected files linked on the pages of each edition.
   */
  public static function files(): array
  {
    if (static::$files !== null) {
      return static::$files;
    }

    $files = [];

    foreach (static::guardedEntries() as $entry) {
      if (!$edition = static::editionForSlug($entry->slug())) {
        continue;
      }

      foreach (static::protectedPaths($entry->data()->all()) as $path) {
        $files[$edition][] = Str::lower(basename($path));
      }
    }

    return static::$files = array_map(
      fn ($paths) => array_values(array_unique($paths)),
      $files
    );
  }

  protected static function guardedEntries()
  {
    $like = str_replace('*', '%', (string) config('blindside.guarded'));

    return Entry::query()
      ->where('collection', 'pages')
      ->where('slug', 'like', $like)
      ->get();
  }

  /**
   * Walk a nested entry value and pull out every "protected/…" asset path.
   */
  protected static function protectedPaths($value): array
  {
    if (is_string($value)) {
      return Str::startsWith($value, 'protected/') ? [$value] : [];
    }

    if (!is_array($value)) {
      return [];
    }

    return collect($value)->flatMap(fn ($v) => static::protectedPaths($v))->all();
  }
}
