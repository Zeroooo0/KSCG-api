<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ForgotPassword extends Notification implements ShouldQueue
{
    use Queueable;
    public $message;
    public $subject;
    public $fromEmail;
    public $mailer;
    /** 
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->message = 'Ovaj link u nastavku je poslat preko forme zaboravljena 
                        šifra na sajtu Karate Saveza Crne Gore i važi 5 min, ukoliko niste vi zatražili 
                        ovu funkciju molimo vas da kontaktirate upravu Karate Saveza.';
        $this->subject = 'Resetovanje zaboravljene šifre';
        $this->fromEmail = 'info@kscg.site';
        $this->mailer = 'smtp';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $notifiable->tokens()->delete();
        $token = $notifiable->createToken('API token of ' . $notifiable->name . ' '. $notifiable->last_name, ['reset'])->plainTextToken;
        $link = env('FRONT_URL'). '?token='. $token;
        return (new MailMessage)
            ->mailer($this->mailer)
            ->subject($this->subject)
            ->greeting('Poštovani '. $notifiable->name . ' '. $notifiable->last_name)
            ->line($this->message)
            ->action( 'Resetuj šifru',$link);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
