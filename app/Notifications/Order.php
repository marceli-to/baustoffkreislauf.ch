<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Order extends Notification
{
  use Queueable;

  protected $data;

  public function __construct($data)
  {
    $this->data = $data;
  }

  public function via($notifiable)
  {
    return ['mail'];
  }

  public function toMail($notifiable)
  {
    return (new MailMessage)
      ->from(env('MAIL_FROM_ADDRESS'))
      ->replyTo(env('MAIL_REPLY_TO_ADDRESS'))
      ->subject('Neue Bestellung: ' . $this->data['title'])
      ->markdown('mail.order.order', ['data' => $this->data]);
  }

  public function toArray($notifiable)
  {
    return [
      //
    ];
  }
}