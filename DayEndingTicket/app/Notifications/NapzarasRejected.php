<?php

namespace App\Notifications;

use App\Models\Napzaras;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NapzarasRejected extends Notification
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
            ->subject('Napzárás elutasítva')
            ->greeting('Kedves ' . $notifiable->name . '!')
            ->line('A(z) ' . $this->napzaras->datum->format('Y-m-d') . ' napra feltöltött napzárásod elutasításra került.')
            ->line('Fiók: ' . $this->napzaras->fiok->nev)
            ->line('Indok: ' . ($this->napzaras->jovahagyas_megjegyzes ?? 'Nincs megadva'))
            ->action('Napzárás megtekintése', route('napzarasok.show', $this->napzaras))
            ->line('Kérjük, ellenőrizd és javítsd a napzárást!');
    }
}