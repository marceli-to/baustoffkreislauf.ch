@php
  // Rows in the YAML are [label, …, text]: everything but the last item is the label.
  $row = fn (array $row) => [implode(', ', array_slice($row, 0, -1)), end($row)];
  $md = fn (?string $text) => \Illuminate\Support\Str::inlineMarkdown($text ?? '');

  $elements = collect($guide['groups'])->flatMap(fn ($group) => $group['elements']);
  $titles = $elements->pluck('title', 'handle');
@endphp
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>{{ $guide['title'] }} – Baustoff Kreislauf Schweiz</title>
  <link rel="stylesheet" href="https://use.typekit.net/iaz8rmb.css">
  <style>{!! file_get_contents(resource_path('redaktion/anleitung.css')) !!}</style>
</head>
<body>

<a class="skip" href="#start">Zum Inhalt</a>

<button class="nav-toggle" type="button" aria-controls="nav" aria-expanded="false">Inhalt</button>

<nav class="nav" id="nav" aria-label="Inhalt der Anleitung">
  <a class="nav__brand" href="#start">
    <img src="/img/logo-bks.png" alt="Baustoff Kreislauf Schweiz" width="180">
    <span>{{ $guide['title'] }}</span>
  </a>

  <label class="nav__search">
    <span class="visually-hidden">Anleitung durchsuchen</span>
    <input type="search" placeholder="Suchen, z.B. Logo, Video" autocomplete="off" data-search>
  </label>
  <p class="nav__empty" data-search-empty hidden>Nichts gefunden. Ein anderes Wort versuchen, z.B. «Datei» statt «PDF».</p>

  <ul class="nav__list">
    <li><a href="#grundlagen">{{ $guide['basics']['title'] }}</a></li>
    <li><a href="#neu">{{ $guide['changes']['title'] }}</a></li>
  </ul>
  @foreach ($guide['groups'] as $group)
    <p class="nav__group">{{ $group['title'] }}</p>
    <ul class="nav__list">
      @foreach ($group['elements'] as $element)
        <li><a href="#{{ $element['handle'] }}">{{ $element['title'] }}</a></li>
      @endforeach
    </ul>
  @endforeach
  <p class="nav__group">Weiteres</p>
  <ul class="nav__list">
    @foreach ($guide['guides'] as $section)
      <li><a href="#{{ $section['handle'] }}">{{ $section['title'] }}</a></li>
    @endforeach
  </ul>

  <a class="nav__back" href="{{ cp_route('index') }}">Zum CMS</a>
</nav>

<main class="main" id="start">

  <header class="intro">
    <h1>Was soll auf die Seite?</h1>
    <p>{{ $guide['intro'] }} Das passende Element finden Sie hier:</p>
  </header>

  <section class="band" aria-label="Elemente nach Zweck">
    <span class="badge" aria-hidden="true">Elemente</span>
    <ul class="finder">
      @foreach ($guide['groups'] as $group)
        @foreach ($group['elements'] as $element)
          <li>
            <a class="tile" href="#{{ $element['handle'] }}" data-search-item="{{ $element['handle'] }}">
              <span class="tile__task">{{ $element['task'] }}</span>
              <span class="tile__element">{{ $element['title'] }}@if ($group['title'] === 'Im Texteditor') <span>im Texteditor</span>@endif</span>
            </a>
          </li>
        @endforeach
      @endforeach
    </ul>
  </section>

  {{-- Basics --}}
  <section class="chapter" id="grundlagen" data-spy>
    <h2>{{ $guide['basics']['title'] }}</h2>
    @include('redaktion.partials.blocks', ['blocks' => $guide['basics']['blocks']])
  </section>

  {{-- What changed --}}
  <section class="chapter" id="neu" data-spy>
    <h2>{{ $guide['changes']['title'] }}</h2>
    <p class="lede">{{ $guide['changes']['text'] }}</p>
    <table class="changes">
      <thead>
        <tr><th scope="col">Bisher</th><th scope="col">Neu</th><th scope="col">Was sich ändert</th></tr>
      </thead>
      <tbody>
        @foreach ($guide['changes']['rows'] as $change)
          <tr>
            <td>{{ implode(', ', array_slice($change, 0, -2)) }}</td>
            <td><a href="#{{ $change[count($change) - 2] }}">{{ $titles[$change[count($change) - 2]] }}</a></td>
            <td>{!! $md(end($change)) !!}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <ul class="list">
      @foreach ($guide['changes']['also'] as $item)
        <li>{!! $md($item) !!}</li>
      @endforeach
    </ul>
  </section>

  {{-- Elements --}}
  @foreach ($guide['groups'] as $group)
    <div class="group" id="{{ $group['handle'] ?? \Illuminate\Support\Str::slug($group['title']) }}">
      <span class="badge badge--sticky" aria-hidden="true">{{ $group['badge'] }}</span>

      <div class="group__body">
        <header class="group__head">
          <h2>{{ $group['title'] }}</h2>
          <p class="lede">{!! $md($group['intro']) !!}</p>
          @isset($group['image'])
            @include('redaktion.partials.figure', ['file' => $group['image'], 'kind' => 'cms', 'note' => $group['caption'], 'live' => null])
          @endisset
        </header>

        @foreach ($group['elements'] as $element)
          <section class="element" id="{{ $element['handle'] }}" data-spy
            data-search-text="{{ implode(' ', array_filter([$element['title'], $element['task'], $element['summary'], collect($element['fields'] ?? [])->flatten()->implode(' '), collect($element['variants'] ?? [])->flatten()->implode(' ')])) }}">
            <h3>{{ $element['title'] }}</h3>
            <p class="summary">{!! $md($element['summary']) !!}</p>

            <div class="website">
              @foreach ($element['web'] as $file)
                @include('redaktion.partials.figure', ['file' => $file, 'kind' => 'web', 'note' => null, 'live' => $loop->last ? ($element['live'] ?? null) : null, 'login' => $element['live_login'] ?? false])
              @endforeach
            </div>

            <div class="howto">
              @include('redaktion.partials.figure', ['file' => $element['cms'], 'kind' => 'cms', 'note' => null, 'live' => null])

              <div class="howto__text">
                @isset($element['variants'])
                  @include('redaktion.partials.rows', ['title' => 'Drei Varianten', 'rows' => $element['variants']])
                @endisset
                @isset($element['fields'])
                  @include('redaktion.partials.rows', ['title' => $element['fields_title'] ?? 'Felder', 'rows' => $element['fields']])
                @endisset
                @include('redaktion.partials.notes', ['tips' => $element['tips'] ?? [], 'warnings' => $element['warnings'] ?? []])
              </div>
            </div>

            @isset($element['extra'])
              <div class="howto">
                @include('redaktion.partials.figure', ['file' => $element['extra']['image'], 'kind' => 'cms', 'note' => null, 'live' => null])
                <div class="howto__text">
                  @include('redaktion.partials.rows', ['title' => $element['extra']['title'], 'rows' => $element['extra']['rows']])
                </div>
              </div>
            @endisset

            @foreach ($element['more'] ?? [] as $more)
              <div class="website">
                @include('redaktion.partials.figure', ['file' => $more['image'], 'kind' => 'web', 'note' => $more['caption'], 'live' => null])
              </div>
            @endforeach
          </section>
        @endforeach
      </div>
    </div>
  @endforeach

  {{-- Posts, page settings, tips --}}
  <div class="group">
    <span class="badge badge--sticky" aria-hidden="true">Weiteres</span>
    <div class="group__body">
      @foreach ($guide['guides'] as $section)
        <section class="element" id="{{ $section['handle'] }}" data-spy>
          <h3>{{ $section['title'] }}</h3>
          @isset($section['text'])
            <p class="summary">{!! $md($section['text']) !!}</p>
          @endisset

          @isset($section['web'])
            <div class="website">
              @include('redaktion.partials.figure', ['file' => $section['web'], 'kind' => 'web', 'note' => null, 'live' => $section['live'] ?? null])
            </div>
          @endisset

          @isset($section['cms'])
            <div class="howto">
              @include('redaktion.partials.figure', ['file' => $section['cms'], 'kind' => 'cms', 'note' => null, 'live' => null])
              <div class="howto__text">
                @include('redaktion.partials.rows', ['title' => 'Felder', 'rows' => $section['fields']])
              </div>
            </div>
          @endisset

          @include('redaktion.partials.blocks', ['blocks' => $section['blocks'] ?? []])
        </section>
      @endforeach
    </div>
  </div>

</main>

<dialog class="zoom" aria-label="Bild vergrössert">
  <form method="dialog">
    <button class="zoom__close" aria-label="Schliessen">
      <svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true"><path d="M2 2l12 12M14 2L2 14" stroke="currentColor" stroke-width="2"/></svg>
    </button>
  </form>
  <img alt="">
</dialog>

<script>{!! file_get_contents(resource_path('redaktion/anleitung.js')) !!}</script>
</body>
</html>
