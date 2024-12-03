<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Facades\Entry;

class UpdateIndex extends Command
{
    protected $signature = 'update:index';
    protected $description = 'Updates the searchable content for all entries';

    public function handle()
    {
        $this->info('Starting index update...');

        // Get all entries from pages collection
        $pages = Entry::query()->where('collection', 'pages')->get();
        $this->updateEntries($pages, 'pages');

        // Get all entries from posts collection
        $posts = Entry::query()->where('collection', 'posts')->get();
        $this->updateEntries($posts, 'posts');

        // Get all entries from events collection
        $events = Entry::query()->where('collection', 'events')->get();
        $this->updateEntries($events, 'events');

        // Update search index
        $this->call('statamic:search:update', ['--all' => true]);

        $this->info('Index update completed!');

        // Send an email notification to m@marceli.to
        \Mail::raw('Search index update completed.', function($message) {
          $message->subject('BKS Update Search Index');
          $message->to('m@marceli.to');
        });
    }

    private function updateEntries($entries, $type)
    {
        foreach ($entries as $entry) {
            $searchableContent = $this->getSearchableContent($entry);
            
            $entry->set('searchable_content', $searchableContent);
            $entry->save();
            
            $this->info("Updated {$type} entry: {$entry->get('title')}");
        }
    }

    private function getSearchableContent($entry)
    {
        $content = [];

        // Add basic fields
        $content[] = $entry->get('title');
        $this->info("Title: " . $entry->get('title'));

        // Handle page elements for pages
        if ($entry->collection()->handle() === 'pages') {
            $pageElements = $entry->get('page_elements');
            
            if (is_array($pageElements)) {
                foreach ($pageElements as $element) {
                    $this->info("Processing element type: " . ($element['type'] ?? 'unknown'));

                    switch ($element['type'] ?? '') {
                        case 'editor':
                            if (isset($element['content'])) {
                                foreach ($element['content'] as $content_block) {
                                    $this->processContentBlock($content_block, $content);
                                }
                            }
                            break;

                        case 'image_text_topic':
                            if (isset($element['text'])) {
                                foreach ($element['text'] as $content_block) {
                                    $this->processContentBlock($content_block, $content);
                                }
                            }
                            break;

                        case 'quote':
                            if (isset($element['quote'])) {
                                $content[] = $element['quote'];
                                $this->info("Added quote content");
                            }
                            break;

                        case 'splash':
                            if (isset($element['text'])) {
                                foreach ($element['text'] as $content_block) {
                                    $this->processContentBlock($content_block, $content);
                                }
                            }
                            break;

                        case 'team':
                            if (isset($element['team_members'])) {
                                foreach ($element['team_members'] as $member) {
                                    $memberInfo = array_filter([
                                        $member['firstname'] ?? '',
                                        $member['name'] ?? '',
                                        $member['position'] ?? '',
                                        $member['email'] ?? ''
                                    ]);
                                    $content = array_merge($content, $memberInfo);
                                    $this->info("Added team member: " . implode(' ', $memberInfo));
                                }
                            }
                            break;
                    }
                }
            }
        }

        // Handle regular content field
        $regularContent = $entry->get('content');
        if ($regularContent) {
            if (is_array($regularContent)) {
                $this->processContentBlock(['content' => $regularContent], $content);
            } else {
                $content[] = $regularContent;
            }
            $this->info("Added content field");
        }

        // Handle description field
        if ($entry->get('description')) {
            $content[] = $entry->get('description');
            $this->info("Added description field");
        }

        // Ensure all content items are strings and filter out empty values
        $content = array_map(function($item) {
            return is_array($item) ? '' : (string)$item;
        }, $content);
        
        // Filter out empty values and join with spaces
        $searchableContent = implode(' ', array_filter($content));
        $this->info("Final content length: " . strlen($searchableContent));
        
        return $searchableContent;
    }

    private function processContentBlock($block, &$content)
    {
        // Handle text directly in the block
        if (isset($block['text'])) {
            $content[] = $block['text'];
            $this->info("Added text: " . substr($block['text'], 0, 50) . "...");
            return;
        }

        // Handle nested content
        if (isset($block['content']) && is_array($block['content'])) {
            foreach ($block['content'] as $item) {
                if (is_array($item)) {
                    // Handle text with marks (like links)
                    if (isset($item['text'])) {
                        $content[] = $item['text'];
                        $this->info("Added marked text: " . substr($item['text'], 0, 50) . "...");
                    }
                    // Recursively process nested content
                    if (isset($item['content'])) {
                        $this->processContentBlock($item, $content);
                    }
                } elseif (is_string($item)) {
                    $content[] = $item;
                    $this->info("Added string content: " . substr($item, 0, 50) . "...");
                }
            }
        }
    }
}
