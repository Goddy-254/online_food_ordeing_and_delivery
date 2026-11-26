<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Send a receipt-style email after the payment callback confirms success. */
class PaymentSuccessfulNotification extends Notification
{
    use Queueable;

    public function __construct(public Payment $payment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $orderId = $this->payment->order_id;
        $amount = number_format((float) $this->payment->amount, 2);

        $mail = (new MailMessage)
            ->subject("Payment received for order #{$orderId}")
            ->greeting('Payment confirmed, '.$notifiable->name.'!')
            ->line("We received KSh {$amount} for order #{$orderId}.");

        if ($this->payment->mpesa_receipt) {
            $mail->line('M-Pesa receipt: '.$this->payment->mpesa_receipt);
        }

        if ($this->payment->paid_at) {
            $mail->line('Received on '.$this->payment->paid_at->timezone('Africa/Nairobi')->format('d M Y, H:i').'.');
        }

        return $mail->line('Thank you for ordering with FoodExpress.');
    }
}
