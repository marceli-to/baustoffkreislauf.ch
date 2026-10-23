@foreach ($blocks as $block)
  <div class="block {{ isset($block['image']) ? 'block--image' : '' }}">
    <div class="block__text">
      @isset($block['title'])
        <h4>{{ $block['title'] }}</h4>
      @endisset
      @isset($block['text'])
        <p>{!! $md($block['text']) !!}</p>
      @endisset
      @isset($block['list'])
        <ul class="list">
          @foreach ($block['list'] as $item)
            <li>{!! $md($item) !!}</li>
          @endforeach
        </ul>
      @endisset
      @isset($block['rows'])
        @include('redaktion.partials.rows', ['title' => null, 'rows' => $block['rows']])
      @endisset
    </div>
    @isset($block['image'])
      @include('redaktion.partials.figure', ['file' => $block['image'], 'kind' => 'cms', 'note' => $block['caption'] ?? null, 'live' => null])
    @endisset
  </div>
@endforeach
