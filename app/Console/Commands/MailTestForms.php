<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use App\Notifications\UserEventRegistration;
use App\Notifications\OwnerEventRegistration;
use App\Notifications\UserCourseRegistration;
use App\Notifications\OwnerCourseRegistration;
use App\Notifications\OrderConfirmation;
use App\Notifications\Order;

class MailTestForms extends Command
{
  protected $signature = 'mail:test-forms';

  protected $description = 'Send test emails for all form types with dummy data';

  public function handle()
  {
    $formType = $this->choice(
      'Which form type would you like to test?',
      ['event', 'course', 'order', 'all'],
      0
    );

    $locale = $this->choice(
      'Which locale?',
      ['de', 'fr', 'it'],
      0
    );

    $email = $this->ask('Enter the email address to send test mails to');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $this->error('Invalid email address.');
      return 1;
    }

    $sendOwnerMail = $this->confirm('Also send owner/admin notification?', true);

    try {
      if ($formType === 'event' || $formType === 'all') {
        $this->sendEventMails($email, $locale, $sendOwnerMail);
      }

      if ($formType === 'course' || $formType === 'all') {
        $this->sendCourseMails($email, $locale, $sendOwnerMail);
      }

      if ($formType === 'order' || $formType === 'all') {
        $this->sendOrderMails($email, $locale, $sendOwnerMail);
      }

      $this->info('Test mails sent successfully!');
      return 0;
    } catch (\Exception $e) {
      $this->error('Failed to send mail: ' . $e->getMessage());
      return 1;
    }
  }

  protected function sendEventMails(string $email, string $locale, bool $sendOwnerMail): void
  {
    $data = [
      'title' => 'Test-Anlass / Événement test / Evento test',
      'event_id' => 'test-event-id',
      'salutation' => 'Herr',
      'language' => 'Deutsch',
      'name' => 'Muster',
      'firstname' => 'Max',
      'email' => $email,
      'phone' => '+41 79 123 45 67',
      'company' => 'Test AG',
      'zip' => '3011',
      'city' => 'Bern',
      'address' => 'Teststrasse 123',
      'remarks' => 'Dies ist ein Testkommentar.',
      'cost_center' => 'KST-12345',
      'party' => 'Testpartei / Organisation',
      'affiliation' => 'Mitglied',
      'wants_meal_options' => 'true',
      'meal_options' => 'Vegetarisch',
      'additional_individuals' => "Maria Muster, Vegan\nPeter Test, Fleisch",
      'locale' => $locale,
    ];

    $this->info('Sending event user registration mail...');
    Notification::route('mail', $email)->notify(new UserEventRegistration($data));

    if ($sendOwnerMail) {
      $this->info('Sending event owner registration mail...');
      Notification::route('mail', $email)->notify(new OwnerEventRegistration($data));
    }
  }

  protected function sendCourseMails(string $email, string $locale, bool $sendOwnerMail): void
  {
    $data = [
      'title' => 'Test-Kurs / Cours test / Corso test',
      'date' => '15. Februar 2026',
      'course_id' => 'test-course-id',
      'salutation' => 'Frau',
      'name' => 'Beispiel',
      'firstname' => 'Anna',
      'email' => $email,
      'phone' => '+41 79 987 65 43',
      'company' => 'Beispiel GmbH',
      'zip' => '8001',
      'city' => 'Zürich',
      'address' => 'Beispielweg 42',
      'remarks' => 'Kurs-Testbemerkung',
      'cost_center' => 'KST-67890',
      'locale' => $locale,
    ];

    $this->info('Sending course user registration mail...');
    Notification::route('mail', $email)->notify(new UserCourseRegistration($data));

    if ($sendOwnerMail) {
      $this->info('Sending course owner registration mail...');
      Notification::route('mail', $email)->notify(new OwnerCourseRegistration($data));
    }
  }

  protected function sendOrderMails(string $email, string $locale, bool $sendOwnerMail): void
  {
    $data = [
      'title' => 'Test-Publikation / Publication test / Pubblicazione test',
      'publication_id' => 'test-publication-id',
      'order_date' => now()->format('Y-m-d'),
      'quantity' => 5,
      'name' => 'Testmann',
      'firstname' => 'Thomas',
      'email' => $email,
      'phone' => '+41 79 111 22 33',
      'company' => 'Testfirma SA',
      'invoice_address' => "Testfirma SA\nRechnungsstrasse 99\n1000 Lausanne",
      'delivery_address' => "Thomas Testmann\nLieferweg 1\n3000 Bern",
      'remarks' => 'Bitte sorgfältig verpacken.',
      'locale' => $locale,
    ];

    $this->info('Sending order confirmation mail...');
    Notification::route('mail', $email)->notify(new OrderConfirmation($data));

    if ($sendOwnerMail) {
      $this->info('Sending order notification mail...');
      Notification::route('mail', $email)->notify(new Order($data));
    }
  }
}
