<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

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

    $mailer = config('mail.default');
    $config = config("mail.mailers.{$mailer}");
    $from = config('mail.from');

    $this->info('Mail configuration:');
    $this->table(
      ['Setting', 'Value'],
      [
        ['Mailer', $mailer],
        ['Host', $config['host'] ?? '-'],
        ['Port', $config['port'] ?? '-'],
        ['Encryption', $config['encryption'] ?? 'none'],
        ['Username', $config['username'] ?? 'none'],
        ['From address', $from['address'] ?? '-'],
        ['From name', $from['name'] ?? '-'],
      ]
    );

    $this->info('Sending test mail to ' . $email . '...');
    $this->newLine();

    try {
      // Enable SMTP debug logging
      $transport = Mail::mailer()->getSymfonyTransport();
      if ($transport instanceof EsmtpTransport) {
        $logger = new \Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream();
        $this->info('SMTP transport detected, sending...');
      }

      $sentMessage = Mail::raw('Dies ist eine Testmail um die neuen Mail-Einstellungen zu überprüfen.', function ($message) use ($email) {
        $message->to($email)
                ->subject('Testmail mit neuer Verbindung');
      });

      $this->info('Test mail sent successfully!');
      $this->newLine();

      // Show message ID as proof of acceptance
      if ($sentMessage) {
        $this->info('Message ID: ' . $sentMessage->getMessageId());
      }

      $this->newLine();
      $this->warn('If you don\'t receive it, check your spam/junk folder.');
      return 0;
    } catch (\Symfony\Component\Mailer\Exception\TransportException $e) {
      $this->error('SMTP Transport error: ' . $e->getMessage());
      $this->newLine();
      $this->warn('Debug: ' . $e->getDebug());
      return 1;
    } catch (\Exception $e) {
      $this->error('Failed to send mail: ' . $e->getMessage());
      $this->error('Exception: ' . get_class($e));
      return 1;
    }
  }
}
