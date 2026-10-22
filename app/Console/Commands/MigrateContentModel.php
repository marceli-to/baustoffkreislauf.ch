<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Statamic\Facades\Entry;

/**
 * Moves content from the sets that were merged for Statamic 6 onto their
 * replacements. Safe to run more than once: it only touches old set types.
 */
class MigrateContentModel extends Command
{
  protected $signature = 'content:migrate-sets {--dry-run : Only report what would change}';

  protected $description = 'Move content from the merged page sets onto their replacements';

  // Sets that only switched on a module and became an option of the "module" set
  protected const MODULES = [
    'events' => 'events',
    'teaser_courses' => 'courses',
    'course_form' => 'course_form',
    'order_form' => 'order_form',
    'publications' => 'publications',
    'search' => 'search',
  ];

  protected array $counts = [];

  public function handle()
  {
    $changed = 0;

    foreach (Entry::all() as $entry) {
      $dirty = false;

      foreach (['page_elements', 'content'] as $field) {
        $value = $entry->get($field);

        if (!is_array($value)) {
          continue;
        }

        $migrated = $field === 'page_elements'
          ? $this->migratePageElements($value)
          : $this->migrateBard($value);

        if ($migrated !== $value) {
          $entry->set($field, $migrated);
          $dirty = true;
        }
      }

      if ($dirty) {
        $changed++;
        $this->line(($this->option('dry-run') ? 'Would migrate ' : 'Migrated ').$entry->locale().' '.$entry->collectionHandle().'/'.$entry->slug());

        if (!$this->option('dry-run')) {
          $entry->saveQuietly();
        }
      }
    }

    foreach ($this->counts as $from => $count) {
      $this->info("{$from}: {$count}");
    }

    $this->info("{$changed} entries ".($this->option('dry-run') ? 'would change.' : 'changed.'));

    return 0;
  }

  protected function migratePageElements(array $items): array
  {
    return array_map(function ($item) {
      $type = $item['type'] ?? null;
      $base = ['id' => $item['id'] ?? $this->id(), 'enabled' => $item['enabled'] ?? true];

      if ($type === 'teaser_collection') {
        $this->count($type);

        return $base + ['type' => 'teasers', 'teaser' => array_map(fn ($id) => [
          'id' => $this->id(),
          'type' => 'teaser_entry',
          'enabled' => true,
          'entry' => $id,
        ], $item['entries'] ?? [])];
      }

      if ($type === 'teaser_services') {
        $this->count($type);

        return $base + ['type' => 'tiles', 'tiles' => array_map(fn ($tile) => [
          'id' => $tile['id'] ?? $this->id(),
          'type' => 'tile',
          'enabled' => $tile['enabled'] ?? true,
          'text' => $tile['text'] ?? null,
          'link' => $tile['url'] ?? null,
        ], $item['teaser'] ?? [])];
      }

      if ($type === 'teaser_topics') {
        $this->count($type);

        return $base + ['type' => 'tiles', 'badge' => 'Fachthemen', 'tiles' => array_map(fn ($topic) => [
          'id' => $topic['id'] ?? $this->id(),
          'type' => 'tile',
          'enabled' => $topic['enabled'] ?? true,
          'text' => $this->titleAndText($topic['title'] ?? null, $topic['description'] ?? null),
        ], $item['topic_preview'] ?? [])];
      }

      if (isset(self::MODULES[$type])) {
        $this->count($type);

        return $base + array_filter([
          'type' => 'module',
          'module' => self::MODULES[$type],
          'title' => $type === 'events' ? ($item['title'] ?? null) : null,
          'intro' => $type === 'teaser_courses' ? ($item['intro'] ?? null) : null,
        ]);
      }

      if ($type === 'image') {
        $this->count('image');

        return $base + ['type' => 'images', 'image' => [$this->imageItem($item)]];
      }

      if ($type === 'editor' && is_array($item['content'] ?? null)) {
        $item['content'] = $this->migrateBard($item['content']);
      }

      return $item;
    }, $items);
  }

  protected function migrateBard(array $nodes): array
  {
    return array_map(function ($node) {
      if (($node['type'] ?? null) !== 'set' || !isset($node['attrs']['values']['type'])) {
        return $node;
      }

      $values = $node['attrs']['values'];

      $migrated = match ($values['type']) {
        'press_release' => array_filter([
          'type' => 'download',
          'title' => $values['title'] ?? null,
          'file' => $values['file'] ?? null,
          'date' => $values['date'] ?? null,
        ]),
        'logo' => array_filter([
          'type' => 'download',
          'title' => $values['title'] ?? null,
          'file' => $values['file'] ?? null,
          'preview_image' => $values['preview_image'] ?? null,
        ]),
        'image' => ['type' => 'images', 'image' => [$this->imageItem($values)]],
        default => null,
      };

      if ($migrated) {
        $this->count('bard '.$values['type']);
        $node['attrs']['values'] = $migrated;
      }

      return $node;
    }, $nodes);
  }

  // A single image always rendered full width, which is what the toggle does in a grid
  protected function imageItem(array $image): array
  {
    return array_filter([
      'id' => $this->id(),
      'type' => 'image',
      'enabled' => true,
      'image' => $image['image'] ?? null,
      'caption' => $image['caption'] ?? null,
      'fullscreen' => true,
    ], fn ($value) => $value !== null);
  }

  protected function titleAndText(?string $title, ?string $text): array
  {
    $nodes = [];

    if ($title) {
      $nodes[] = ['type' => 'heading', 'attrs' => ['level' => 3], 'content' => [['type' => 'text', 'text' => $title]]];
    }

    if ($text) {
      $content = [];

      foreach (preg_split('/\R/', trim($text)) as $i => $line) {
        if ($i > 0) {
          $content[] = ['type' => 'hardBreak'];
        }

        if ($line !== '') {
          $content[] = ['type' => 'text', 'text' => $line];
        }
      }

      $nodes[] = ['type' => 'paragraph', 'content' => $content];
    }

    return $nodes;
  }

  protected function count(string $type): void
  {
    $this->counts[$type] = ($this->counts[$type] ?? 0) + 1;
  }

  protected function id(): string
  {
    return strtolower(Str::random(8));
  }
}
