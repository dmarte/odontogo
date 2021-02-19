<?php

namespace App\Notifications;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class BudgetSendNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Collection $budgets,
        private string $subject,
        private string $message,
        private User $author
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  \App\Models\Contact  $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->subject("{$this->author->team->name} / {$this->subject} ({$this->budgets->pluck('code')->join(', ')})")
            ->replyTo($this->author->email)
            ->line($this->message)
            ->line(__('We appreciate you\'re part of us.'))
        ;
        $mail->viewData['team'] = $this->author->team;
        $mail->viewData['author'] = $this->author;

        $this->budgets->each(function (Budget $budget) use ($mail, $notifiable) {
            $mail->attach($budget->pdf->store(), [
                'as'   => "{$budget->team_id}_{$notifiable->code}_{$budget->code}.pdf",
                'mime' => 'application/pdf',
            ]);
        });

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'notifiable' => $notifiable->name,
            'author'     => $this->author->toArray(),
            'subject'    => $this->subject,
            'message'    => $this->message,
            'budgets'    => $this->budgets->toArray(),
        ];
    }
}
