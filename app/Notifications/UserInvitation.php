<?php

namespace App\Notifications;

use App\Models\Team;
use App\Models\User;
use App\Pivots\TeamUser;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserInvitation extends Notification
{
    use Queueable;

    public function __construct(private User $user, private Team $team, private TeamUser $membership, private string $password)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->greeting(__('Hello user', ['name' => $this->user->name]))
            ->line(__('Invitation line 1', ['author' => $this->team->user->name, 'team' => $this->team->name]))
            ->line(__('Invitation line 2', ['password'=> $this->password]))
            ->action(__('Activate my membership'), route('invitation.join', ['team' => $this->team->id, 'token' => $this->membership->token]))
            ->line(__('Thank you for using our application'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}