<x-mail::message>
  <div class="text-base pb-base">
    Guten Tag<br><br>Vielen Dank für Ihre Anmeldung.
  </div>
  <br>
  @if ($data['salutation'])
    <div class="text-base pb-base">
      <strong>Anrede</strong><br>
      {{ $data['salutation'] }}
    </div>
  @endif
  @if ($data['firstname'])
    <div class="text-base pb-base">
      <strong>Vorname</strong><br>
      {{ $data['firstname'] }}
    </div>
  @endif
  @if ($data['name'])
    <div class="text-base pb-base">
      <strong>Name</strong><br>
      {{ $data['name'] }}
    </div>
  @endif
  @if ($data['email'])
    <div class="text-base pb-base">
      <strong>E-Mail</strong><br>
      {{ $data['email'] }}
    </div>
  @endif
  @if ($data['phone'])
    <div class="text-base pb-base">
      <strong>Telefon</strong><br>
      {{ $data['phone'] }}
    </div>
  @endif
  @if ($data['company'])
    <div class="text-base pb-base">
      <strong>Firma</strong><br>
      {{ $data['company'] }}
    </div>
  @endif
  @if ($data['address'])
    <div class="text-base pb-base">
      <strong>Adresse</strong><br>
      {{ $data['address'] }}
    </div>
  @endif
  @if ($data['location'])
    <div class="text-base pb-base">
      <strong>Ort</strong><br>
      {{ $data['location'] }}
    </div>
  @endif
  @if ($data['meal_options'])
    <div class="text-base pb-base">
      <strong>Verpflegung</strong><br>
      {{ $data['meal_options'] }}
    </div>
  @endif
  @if ($data['additional_individuals'])
    <div class="text-base pb-base">
      <strong>Zusätzliche Teilnehmer</strong><br>
      {{ $data['additional_individuals'] }}
    </div>
  @endif
  <footer>
    Baustoff Kreislauf Schweiz<br>Schwanengasse 12<br>3011 Bern
  </footer>
</x-mail::message>
