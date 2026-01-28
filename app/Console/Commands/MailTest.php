<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MailTest extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'mail:test';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Send a test email to verify mail settings.';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $email = $this->ask('Enter the email address to send the test mail to');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $this->error('Invalid email address.');
      return 1;
    }

    $this->info('Sending test mail to ' . $email . '...');

    try {
      Mail::raw('Dies ist eine Testmail um die neuen Mail-Einstellungen zu überprüfen.', function ($message) use ($email) {
        $message->to($email)
                ->subject('Testmail mit neuer Verbindung');
      });

      $this->info('Test mail sent successfully!');
      return 0;
    } catch (\Exception $e) {
      $this->error('Failed to send mail: ' . $e->getMessage());
      return 1;
    }
  }
}
