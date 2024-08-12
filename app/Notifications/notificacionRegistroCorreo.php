<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class notificacionRegistroCorreo extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        //dd($notifiable);
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
                    ->subject('Expedientes ITO - Verifica tu correo electrónico')
                    ->line('Se te ha creado una cuenta en la plataforma de Expedientes ITO')
                    ->line('Haz clic en el botón de abajo para verificar tu dirección de correo electrónico.')
                    ->action('Verificar correo electrónico', $verificationUrl)
                    ->line('Si no creaste una cuenta, no se requiere ninguna otra acción.');  
    }

    protected function verificationUrl($notifiable)
    {

        return URL::signedRoute(
            'verification.verify2', 
            [
                'id' =>  $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
