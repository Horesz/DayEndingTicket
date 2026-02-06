<?php

namespace App\Notifications;

use App\Models\Napzaras;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NapzarasApproved extends Notification
{
    use Queueable;

    protected $napzaras;

    public function __construct(Napzaras $napzaras)
    {
        $this->napzaras = $napzaras;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Napzárás jóváhagyva')
            ->greeting('Kedves ' . $notifiable->name . '!')
            ->line('A(z) ' . $this->napzaras->datum->format('Y-m-d') . ' napra feltöltött napzárásod jóváhagyásra került.')
            ->line('Fiók: ' . $this->napzaras->fiok->nev)
            ->line('Jóváhagyta: ' . $this->napzaras->jovahagyo->name)
            ->action('Napzárás megtekintése', route('napzarasok.show', $this->napzaras))
            ->line('Köszönjük a pontos munkát!');
    }
}