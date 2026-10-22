<?php

/*
 * Demo pages for the editor guide in the CP (resources/redaktion).
 *
 * Creates one hidden page per content element below «Doku Inhaltselemente»
 * (/doku-inhaltselemente), using real texts and assets from the site. The pages
 * are not in any navigation. Safe to run again: existing demo pages are
 * overwritten. Local only – do not run on production.
 *
 *     php scripts/docs/demo-content.php          # create / update
 *     php scripts/docs/demo-content.php --delete # remove the demo pages
 */

use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Fieldtypes\Bard;
use Statamic\Fieldtypes\Bard\Augmentor;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

const PARENT_ID = 'doku-inhaltselemente';

// Bard value from HTML strings and set arrays (['type' => 'download', ...]).
function bard(...$parts): array
{
    $augmentor = new Augmentor(new Bard);
    $nodes = [];
    foreach ($parts as $part) {
        if (is_string($part)) {
            array_push($nodes, ...$augmentor->renderHtmlToProsemirror($part)['content']);
        } else {
            $nodes[] = ['type' => 'set', 'attrs' => ['id' => uid(), 'values' => $part]];
        }
    }

    return $nodes;
}

function uid(): string
{
    return strtolower(substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(12))), 0, 8));
}

// Replicator item.
function item(string $type, array $values = []): array
{
    return ['id' => uid(), 'type' => $type, 'enabled' => true] + $values;
}

// A page element copied from an existing page, so the demo matches real content.
function copyElement(string $entryId, string $type): array
{
    $element = collect(Entry::find($entryId)->get('page_elements'))->firstWhere('type', $type);

    return array_merge($element, ['id' => uid(), 'enabled' => true]);
}

function lorem(): string
{
    return 'Mineralische Baustoffe lassen sich nach dem Rückbau aufbereiten und erneut einsetzen. '
        .'So bleiben Rohstoffe im Kreislauf, Deponievolumen wird geschont und Transportwege werden kürzer.';
}

// Sponsors of an earlier Baustofftage edition, three logos per group.
function sponsors(): array
{
    $element = copyElement('47ddee3b-db7f-4e43-8749-169431f59c09', 'sponsors');
    $element['title'] = 'Sponsoren';
    $element['sponsor_group'] = collect($element['sponsor_group'])->take(2)->map(function ($group) {
        $group['group_title'] = explode(' / ', $group['group_title'])[0];
        $group['logos'] = array_slice($group['logos'], 0, $group['logo_grid'] == 6 ? 2 : 3);

        return $group;
    })->all();

    return $element;
}

$pages = [
    'texteditor' => ['Texteditor', [
        item('editor', ['has_indent' => false, 'content' => bard(
            '<h1>Kreislaufwirtschaft im Bau</h1>',
            '<p class="lead">Ein Lead ist der fett gesetzte Einstieg in eine Seite. Er fasst in ein, zwei Sätzen zusammen, worum es geht.</p>',
            '<p>'.lorem().' Mehr dazu steht in den <a href="https://www.baustoffkreislauf.ch">Fachthemen</a>. Wichtige Begriffe können <strong>fett</strong> oder <em>kursiv</em> hervorgehoben werden.</p>',
            '<h2>Zwischentitel (H2)</h2>',
            '<p>Aufzählungen eignen sich für kurze, gleichwertige Punkte:</p>',
            '<ul><li><p>Rückbau und Trennung der Materialien</p></li><li><p>Aufbereitung im Recyclingwerk</p></li><li><p>Wiedereinsatz als Recyclingbaustoff</p></li></ul>',
            '<h3>Untertitel (H3)</h3>',
            '<p>'.lorem().'</p>',
        )]),
    ]],
    'texteditor-einzug' => ['Texteditor mit Texteinzug', [
        item('editor', ['has_indent' => true, 'content' => bard(
            '<h1>Texteditor mit Texteinzug</h1>',
            '<p class="lead">Mit «Texteinzug» rückt der Text ein, nur der Haupttitel bleibt links bündig.</p>',
            '<p>'.lorem().' '.lorem().'</p>',
            '<h2>Zwischentitel</h2>',
            '<p>'.lorem().'</p>',
        )]),
    ]],
    'download' => ['Download', [
        item('editor', ['content' => bard(
            '<h2>Medienmitteilungen</h2>',
            ['type' => 'download', 'title' => 'Medienmitteilung «Baustoff Kreislauf Schweiz»', 'file' => 'downloads/medienmitteilung_baustoffkreislaufschweiz_03052024.pdf', 'date' => '2024-05-03'],
            '<h2>Dokumente</h2>',
            ['type' => 'download', 'title' => 'Traktandenliste Mitgliederversammlung', 'file' => 'downloads/medienmitteilung_baustoffkreislaufschweiz_03052024.pdf'],
            '<h2>Logos</h2>',
            ['type' => 'download', 'title' => 'Logo mit Claim, DE', 'file' => 'downloads/logo_baustoff-kreislauf-schweiz_de_mit-claim_farbig_cmyk.eps.zip', 'preview_image' => 'downloads/logo_baustoff-kreislauf-schweiz_de_mit-claim_farbig_rgb.png'],
            ['type' => 'download', 'title' => 'Logo mit Claim, FR', 'file' => 'downloads/logo_baustoff-kreislauf-schweiz_fr_mit-claim_farbig_cmyk.eps.zip', 'preview_image' => 'downloads/logo_baustoff-kreislauf-schweiz_fr_mit-claim_farbig_rgb.png'],
        )]),
    ]],
    'datei-geschuetzt' => ['Datei (geschützt)', [
        item('editor', ['content' => bard(
            '<h2>Unterlagen für Teilnehmende</h2>',
            ['type' => 'protected_file', 'description' => 'Download Programmheft', 'file' => 'protected/baustofftage-2025_programmheft-de-def.pdf', 'remark' => 'Der Download ist nur nach dem Login möglich.'],
        )]),
    ]],
    'bilder-download' => ['Bilder Download', [
        item('editor', ['content' => bard(
            '<h2>Bilder</h2>',
            ['type' => 'image_downloads', 'images' => [
                item('image', ['image_download' => 'downloads/baustoffkreislauf-praesidium.jpg', 'hires_image' => 'downloads/baustoffkreislauf-praesidium.jpg.zip', 'caption' => 'Das Präsidium von «Baustoff Kreislauf Schweiz».']),
                item('image', ['image_download' => 'downloads/baustoffkreislauf-vorstand-und-co-geschaeftsfuehrer.jpg', 'hires_image' => 'downloads/baustoffkreislauf-vorstand-und-co-geschaeftsfuehrer.jpg.zip', 'caption' => 'Der Vorstand und Co-Geschäftsführer von «Baustoff Kreislauf Schweiz».']),
            ]],
        )]),
    ]],
    'button' => ['Button (Kachel)', [
        item('editor', ['content' => bard(
            '<h2>Weiterführende Links</h2>',
            ['type' => 'button_panel', 'link_url' => 'https://www.abfall-rohstoff.ch/berater-spezialisten', 'link_text' => 'Liste der Altlastenberater und Altlastenberaterinnen – OdA Abfall und Rohstoffwirtschaft', 'target' => '_blank'],
            ['type' => 'button_panel', 'link_url' => 'entry::c3e8cb52-10bb-4644-973c-065ec7990984', 'link_text' => 'Zu den Medienmitteilungen', 'target' => '_self'],
        )]),
    ]],
    'newsletter-formular' => ['Newsletter Formular', [
        item('editor', ['content' => bard(
            '<h2>Newsletter abonnieren</h2>',
            '<p>Erhalten Sie die Mitglieder-Infos per E-Mail.</p>',
            ['type' => 'newsletter_form'],
        )]),
    ]],
    'embed-code' => ['Embed Code', [
        item('editor', ['content' => bard(
            '<h2>Standort</h2>',
            ['type' => 'embed_code', 'code' => '<iframe src="https://www.openstreetmap.org/export/embed.html?bbox=7.42%2C46.94%2C7.46%2C46.96&amp;layer=mapnik" style="width:100%;height:360px;border:0"></iframe>'],
        )]),
    ]],
    'teaser' => ['Teaser', [
        item('teasers', ['teaser' => [
            item('teaser', ['has_border_top' => true, 'image' => '_dsf2025.jpg', 'image_ratio' => 'landscape', 'title' => 'Manueller Teaser', 'text' => bard('<p>Bild, Titel, Text und Link werden direkt im Teaser erfasst.</p>'), 'link' => 'entry::c3e8cb52-10bb-4644-973c-065ec7990984', 'link_text' => 'Zu den Medien']),
            item('teaser_post', ['post' => ['dafbbf5d-94bc-4c10-9353-7df596c8fe77'], 'image_ratio' => 'landscape']),
            item('teaser_entry', ['entry' => '241ac301-06fb-4c53-996a-72ccc44031df']),
        ]]),
    ]],
    'kacheln' => ['Kacheln', [
        item('tiles', ['badge' => 'Fachthemen', 'tiles' => [
            item('tile', ['text' => bard('<h3>Arbeitshilfen &amp; Publikationen</h3><p>Merkblätter, Richtlinien und Fachpublikationen.</p>'), 'link' => 'entry::e5929a57-c53a-4f5d-9aa2-147e773fbf9f']),
            item('tile', ['text' => bard('<h3>Bildung</h3><p>Kurse und Weiterbildungen für die Branche.</p>'), 'link' => 'entry::fe11d384-7568-40ee-9b41-4858a866f88d']),
            item('tile', ['text' => bard('<p>Natur &amp; Boden</p>'), 'link' => 'entry::3e8f3f3a-474e-4b11-9172-e3162f4bf1dd']),
            item('tile', ['text' => bard('<h3>Ohne Link</h3><p>Eine Kachel ohne Link ist nicht klickbar.</p>')]),
        ]]),
    ]],
    'beitraege' => ['Beiträge verknüpfen', [
        item('list_posts', ['category' => 'aktuell', 'is_archive' => false]),
    ]],
    'bilder' => ['Bilder', [
        item('images', ['image' => [
            item('image', ['image' => '_dsf2025.jpg', 'caption' => 'Ein Bild mit «Ganze Breite» im Format 16:9.', 'fullscreen' => true]),
        ]]),
        item('images', ['image' => [
            item('image', ['image' => 'ruckbauweb.jpg', 'caption' => 'Rückbau', 'fullscreen' => false]),
            item('image', ['image' => '_dsf8929.jpg', 'caption' => 'Aufbereitung', 'fullscreen' => false]),
            item('image', ['image' => '_dsc4416_1.jpg', 'caption' => 'Wiedereinsatz', 'fullscreen' => false]),
        ]]),
    ]],
    'bild-text' => ['Bild/Text (Fachbereich)', [
        copyElement('25333735-482d-4fc3-92ff-b580289daaab', 'image_text_topic'),
    ]],
    'video' => ['Video', [
        copyElement('13cd2b63-fc01-4b66-a7d2-54f5c7e837e6', 'video'),
    ]],
    'modul' => ['Modul', [
        item('module', ['module' => 'events', 'title' => 'Anlässe']),
    ]],
    'team' => ['Team', [
        item('team', ['text' => bard('<p>Die Geschäftsstelle ist Ihre Anlaufstelle für alle Fragen rund um den Verband.</p>'), 'team_members' => array_slice(copyElement('66c763f9-e921-4075-9e99-6c69c7e09ce3', 'team')['team_members'], 0, 4)]),
    ]],
    'sponsoren' => ['Sponsoren', [
        sponsors(),
    ]],
    'stoerer' => ['Störer', [
        item('splash', ['hint_federation' => false, 'intro' => bard('<p>Optionaler Einleitungstext oberhalb der Box.</p>'), 'text' => bard('<h2>Jetzt Mitglied werden</h2><p class="text-md md:text-lg lg:text-xl leading-[1.2]">Profitieren Sie vom Netzwerk der Kies-, Beton- und Recyclingbranche.</p>'), 'has_link' => true, 'link' => 'entry::c3e8cb52-10bb-4644-973c-065ec7990984', 'link_text' => 'Mehr erfahren', 'logo' => 'baustoffkreislauf-logo-fskb.png']),
        item('splash', ['hint_federation' => true]),
    ]],
    'zitat' => ['Zitat', [
        item('quote', ['quote' => "Wer Baustoffe im Kreislauf hält,\nschont Ressourcen und Landschaft."]),
    ]],
    'interaktives-svg' => ['Interaktives SVG', [
        copyElement('a033a788-7f75-47ec-81b2-9190608d767d', 'interactive_svg'),
    ]],
];

$collection = Collection::find('pages');
$tree = $collection->structure()->in('de');

if (in_array('--delete', $argv)) {
    foreach (array_keys($pages) as $slug) {
        Entry::find('doku-'.$slug)?->delete();
    }
    Entry::find(PARENT_ID)?->delete();
    echo "Demo pages removed.\n";

    exit;
}

$parent = Entry::find(PARENT_ID) ?? Entry::make()->id(PARENT_ID)->collection('pages')->locale('de');
$parent->slug('doku-inhaltselemente')->published(true)->data([
    'title' => 'Doku Inhaltselemente',
    'page_elements' => [item('editor', ['content' => bard(
        '<h1>Doku Inhaltselemente</h1>',
        '<p>Beispielseiten für die Redaktionsanleitung. Diese Seiten sind lokal und erscheinen in keiner Navigation.</p>',
        '<ul>'.collect($pages)->map(fn ($page, $slug) => '<li><p><a href="/doku-inhaltselemente/'.$slug.'">'.e($page[0]).'</a></p></li>')->implode('').'</ul>',
    )])],
])->save();

$branch = ['entry' => PARENT_ID, 'children' => []];
foreach ($pages as $slug => [$title, $elements]) {
    $entry = Entry::find('doku-'.$slug) ?? Entry::make()->id('doku-'.$slug)->collection('pages')->locale('de');
    $entry->slug($slug)->published(true)->data([
        'title' => $title,
        'page_elements' => $elements,
    ])->save();
    $branch['children'][] = ['entry' => 'doku-'.$slug];
}

$branches = collect($tree->tree())->reject(fn ($b) => $b['entry'] === PARENT_ID)->values()->push($branch)->all();
$tree->tree($branches)->save();

echo count($pages)." demo pages saved below /doku-inhaltselemente\n";
