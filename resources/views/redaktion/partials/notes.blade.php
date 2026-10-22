@foreach ($warnings as $note)
  <p class="note note--warn"><strong>Wichtig:</strong> {!! $md($note) !!}</p>
@endforeach
@foreach ($tips as $note)
  <p class="note"><strong>Tipp:</strong> {!! $md($note) !!}</p>
@endforeach
