@php
  // Screenshots are taken at 2x; long CMS forms are shown cut off and open whole in the zoom.
  $path = resource_path('redaktion/bilder/'.$file);
  [$width, $height] = is_file($path) ? getimagesize($path) : [2, 2];
  $tall = $kind === 'cms' && $height / $width > 1.45;
  $label = $kind === 'web' ? 'Website' : 'CMS';
@endphp
<figure class="shot shot--{{ $kind }} {{ $tall ? 'shot--tall' : '' }}">
  <button class="shot__frame" type="button" data-zoom aria-label="{{ $label }}: {{ $note ?? 'Screenshot' }} vergrössern">
    <img src="{{ cp_route('guide.image', $file) }}?v={{ is_file($path) ? filemtime($path) : 0 }}"
      width="{{ intdiv($width, 2) }}" height="{{ intdiv($height, 2) }}" loading="lazy" alt="">
  </button>
  <figcaption>
    <span class="shot__label">{{ $label }}</span>
    @if (!empty($note))
      <span>{{ $note }}</span>
    @endif
    @if (!empty($live))
      <span>Im Einsatz: <a href="https://www.baustoffkreislauf.ch{{ $live }}" target="_blank" rel="noopener">baustoffkreislauf.ch{{ $live }}</a>@if (!empty($login)) (mit Login)@endif</span>
    @endif
    @if ($tall)
      <button class="shot__more" type="button" data-zoom-target>Ganzes Formular ansehen</button>
    @endif
  </figcaption>
</figure>
