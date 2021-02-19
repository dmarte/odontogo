<?php

namespace App\Notifications;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class BudgetSentNotifyUser extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(private Collection $budgets, private Contact $receiver)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * @param $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return \Mirovit\NovaNotifications\Notification::make()
            ->info(__('Budget sent'))
            ->subtitle(__('The list of budgets :code where sent on :date by :user', [
                'code' => $this->budgets->pluck('code')->join(','),
                'date' => now()->setTimezone($notifiable->time_zone)->format('d/m/Y h:i A'),
                'user' => $notifiable->name,
                'client'=> $this->receiver->name,
            ]))
            ->toArray();
    }
}
