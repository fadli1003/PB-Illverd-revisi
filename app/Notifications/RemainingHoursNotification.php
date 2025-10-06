<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RemainingHoursNotification extends Notification
{
    use Queueable;

    protected $booking;

    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Peringatan: Sisa Jam Sewa Anda Hampir Habis')
            ->line("Sisa jam sewa Anda tinggal {$this->booking->remaining_hours} jam.")
            ->line('Silakan perpanjang member Anda untuk melanjutkan layanan.')
            ->action('Perpanjang Sekarang', url('/bookings/membershipUpdate/{id}'));
    }
}