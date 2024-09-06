<x-mail::message>
  <div class="text-base pb-base">
    {!! __('Guten Tag<br><br>Vielen Dank für Ihre Anmeldung.', [], $data['locale']) !!}
  </div>
  <br>
  @if ($data['salutation'])
    <div class="text-base pb-base">
      <strong>{{ __('Anrede', [], $data['locale']) }}</strong><br>
      {{ __($data['salutation'], [], $data['locale']) }}
    </div>
  @endif
  @if ($data['firstname'])
    <div class="text-base pb-base">
      <strong>{{ __('Vorname', [], $data['locale']) }}</strong><br>
      {{ $data['firstname'] }}
    </div>
  @endif
  @if ($data['name'])
    <div class="text-base pb-base">
      <strong>{{ __('Name', [], $data['locale']) }}</strong><br>
      {{ $data['name'] }}
    </div>
  @endif
  @if ($data['email'])
    <div class="text-base pb-base">
      <strong>{{ __('E-Mail', [], $data['locale']) }}</strong><br>
      {{ $data['email'] }}
    </div>
  @endif
  @if ($data['phone'])
    <div class="text-base pb-base">
      <strong>{{ __('Telefon', [], $data['locale']) }}</strong><br>
      {{ $data['phone'] }}
    </div>
  @endif
  @if ($data['company'])
    <div class="text-base pb-base">
      <strong>{{ __('Firma', [], $data['locale']) }}</strong><br>
      {{ $data['company'] }}
    </div>
  @endif
  @if ($data['address'])
    <div class="text-base pb-base">
      <strong>{{ __('Strasse, Nr.', [], $data['locale']) }}</strong><br>
      {{ $data['address'] }}
    </div>
  @endif
  @if ($data['location'])
    <div class="text-base pb-base">
      <strong>{{ __('PLZ/Ort', [], $data['locale']) }}</strong><br>
      {{ $data['location'] }}
    </div>
  @endif
  @if ($data['meal_options'])
    <div class="text-base pb-base">
      <strong>{{ __('Verpflegung', [], $data['locale']) }}</strong><br>
      {{ $data['meal_options'] }}
    </div>
  @endif
  @if ($data['additional_individuals'])
    <div class="text-base pb-base">
      <strong>{{ __('Zusätzliche Teilnehmer', [], $data['locale']) }}</strong><br>
      {{ $data['additional_individuals'] }}
    </div>
  @endif
  <footer>
    {!! __('<strong>Baustoff Kreislauf Schweiz</strong><br>Schwanengasse 12<br>3011 Bern', [], $data['locale']) !!}
  </footer>
</x-mail::message>
