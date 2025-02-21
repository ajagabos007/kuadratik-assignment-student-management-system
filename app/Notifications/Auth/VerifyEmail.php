<?php

namespace App\Notifications\Auth;

use App\Models\VerificationToken;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Lang;

class VerifyEmail extends Notification
{
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(VerificationToken $v_token)
    {
        $this->verification_token = $v_token;
    }
    
    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $greeting = Lang::get('Hello');

        if(($user=$this->verification_token->verificationTokenable) instanceof User)
        {
            $greeting .=" ". \strlen($user->name) > 0 ? $user->name : '!';
        }
        else $greeting .="!";

        return (new MailMessage)
            ->subject(Lang::get('Verify Email Address'))
            ->greeting($greeting)
            ->line(Lang::get('Please use OTP code below to verify your email address.'))
            ->line(Lang::get('Your OTP code is: ') . $this->verification_token->token)
            ->line(Lang::get('If you did not create an account, no further action is required.'));
    }
}
