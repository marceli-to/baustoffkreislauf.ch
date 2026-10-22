@if (!empty($title))
  <h4>{{ $title }}</h4>
@endif
<dl class="rows">
  @foreach ($rows as $item)
    @php [$label, $text] = $row($item); @endphp
    <div>
      <dt>{!! $label !!}</dt>
      <dd>{!! $md($text) !!}</dd>
    </div>
  @endforeach
</dl>
