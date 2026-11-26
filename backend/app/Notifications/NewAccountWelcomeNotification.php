<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Welcome the customer after their account has been created. */
class NewAccountWelcomeNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to FoodExpress')
            ->greeting('Welcome, '.$notifiable->name.'!')
            ->line('Your FoodExpress account has been created successfully.')
            ->line('You can now browse the menu, place an order, and follow its progress.');
    }
}
