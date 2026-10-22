@php
  // Screenshots are taken at 2x; long CMS forms are shown cut off with a button to see them whole.
  $path = resource_path('redaktion/bilder/'.$file);
  [$width, $height] = is_file($path) ? getimagesize($path) : [2, 2];
  $tall = $kind === 'cms' && $height / $width > 1.45;
@endphp
<figure class="shot shot--{{ $kind }} {{ $tall ? 'shot--tall' : '' }}">
  <button class="shot__frame" type="button" data-zoom aria-label="{{ $caption }} vergrössern">
    <img src="{{ cp_route('guide.image', $file) }}?v={{ is_file($path) ? filemtime($path) : 0 }}"
      width="{{ intdiv($width, 2) }}" height="{{ intdiv($height, 2) }}" loading="lazy" alt="">
  </button>
  <figcaption>
    {{ $caption }}
    @if ($tall)
      <button class="shot__more" type="button" data-zoom-target>Ganzes Formular ansehen</button>
    @endif
  </figcaption>
</figure>
