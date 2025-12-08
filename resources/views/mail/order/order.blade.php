<x-mail::message>
  <div class="text-base">
    {!! __('Neue Bestellung.', [], $data['locale']) !!}
  </div>
  <br>
  <div class="text-base">
    <strong>{{ __('Artikel', [], $data['locale']) }}</strong><br>
    {{ $data['title'] }}
  </div>
  <br>
  <div class="text-base">
    <strong>{{ __('Anzahl', [], $data['locale']) }}</strong><br>
    {{ $data['quantity'] }}
  </div>
  <br>
  @if ($data['firstname'])
    <div class="text-base">
      <strong>{{ __('Vorname', [], $data['locale']) }}</strong><br>
      {{ $data['firstname'] }}
    </div>
    <br>
  @endif
  @if ($data['name'])
    <div class="text-base">
      <strong>{{ __('Name', [], $data['locale']) }}</strong><br>
      {{ $data['name'] }}
    </div>
    <br>
  @endif
  @if ($data['email'])
    <div class="text-base">
      <strong>{{ __('E-Mail', [], $data['locale']) }}</strong><br>
      {{ $data['email'] }}
    </div>
    <br>
  @endif
  @if ($data['phone'])
    <div class="text-base">
      <strong>{{ __('Telefon', [], $data['locale']) }}</strong><br>
      {{ $data['phone'] }}
    </div>
    <br>
  @endif
  @if ($data['company'])
    <div class="text-base">
      <strong>{{ __('Firma', [], $data['locale']) }}</strong><br>
      {{ $data['company'] }}
    </div>
    <br>
  @endif
  @if ($data['invoice_address'])
    <div class="text-base">
      <strong>{{ __('Rechnungsadresse', [], $data['locale']) }}</strong><br>
      {!! nl2br($data['invoice_address']) !!}
    </div>
    <br>
  @endif
  <div class="text-base">
    <strong>{{ __('Lieferadresse', [], $data['locale']) }}</strong><br>
    {!! $data['delivery_address'] ? nl2br($data['delivery_address']) : '–' !!}
  </div>
  <br>
  @if ($data['remarks'])
    <div class="text-base">
      <strong>{{ __('Bemerkungen', [], $data['locale']) }}</strong><br>
      {!! nl2br($data['remarks']) !!}
    </div>
    <br>
  @endif
  <footer>
    {!! __('Baustoff Kreislauf Schweiz<br>Schwanengasse 12<br>3011 Bern', [], $data['locale']) !!}
  </footer>
</x-mail::message>
