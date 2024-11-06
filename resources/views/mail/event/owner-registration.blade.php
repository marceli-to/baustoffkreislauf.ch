<x-mail::message>
  <div class="text-base">
    Neue Anmeldung
  </div>
  <br>
  <div class="text-base">
    <strong>{{ __('Anlass', [], $data['locale']) }}</strong><br>
    {{ __($data['title'], [], $data['locale']) }}
  </div>
  <br>
  @if ($data['salutation'])
    <div class="text-base">
      <strong>{{ __('Anrede', [], $data['locale']) }}</strong><br>
      {{ __($data['salutation'], [], $data['locale']) }}
    </div>
    <br>
  @endif
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
  @if ($data['address'])
    <div class="text-base">
      <strong>{{ __('Strasse, Nr.', [], $data['locale']) }}</strong><br>
      {{ $data['address'] }}
    </div>
    <br>
  @endif
  @if ($data['location'])
    <div class="text-base">
      <strong>{{ __('PLZ/Ort', [], $data['locale']) }}</strong><br>
      {{ $data['location'] }}
    </div>
    <br>
  @endif
  @if ($data['cost_center'])
    <div class="text-base">
      <strong>{{ __('Kostenstelle', [], $data['locale']) }}</strong><br>
      {{ $data['cost_center'] }}
    </div>
    <br>
  @endif
  @if ($data['remarks'])
    <div class="text-base">
      <strong>{{ __('Bemerkungen', [], $data['locale']) }}</strong><br>
      {{ $data['remarks'] }}
    </div>
    <br>
  @endif
  @if ($data['meal_options'])
    <div class="text-base">
      <strong>{{ __('Verpflegung', [], $data['locale']) }}</strong><br>
      {{ $data['meal_options'] }}
    </div>
    <br>
  @endif
  @if ($data['additional_individuals'])
    <div class="text-base">
      <strong>{{ __('Zusätzliche Teilnehmer', [], $data['locale']) }}</strong><br>
      {!! nl2br($data['additional_individuals']) !!}
    </div>
    <br>
  @endif
  <footer>
    {!! __('Baustoff Kreislauf Schweiz<br>Schwanengasse 12<br>3011 Bern', [], $data['locale']) !!}
  </footer>
</x-mail::message>