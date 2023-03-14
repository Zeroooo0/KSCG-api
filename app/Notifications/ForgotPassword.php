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
        $this->message = 'Zaboravljena šifra';
        $this->subject = 'Resetovanje šifre putem linka';
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
        $link = env('FRONT_URL'). '?token='. $notifiable->createToken('API token of ' . $notifiable->name . ' '. $notifiable->last_name, ['reset'])->plainTextToken;
        return (new MailMessage)
            ->mailer($this->mailer)
            ->subject($this->subject)
            ->greeting('Hello '. $notifiable->name)
            ->line('Hello '. $notifiable->name)
            ->action( 'Link za resetovanje šifre na vašem nalogu:',$link);
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
