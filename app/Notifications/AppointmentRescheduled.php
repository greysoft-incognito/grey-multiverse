<?php

namespace App\Notifications;

use App\Models\BizMatch\Reschedule;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentRescheduled extends Notification
{
    use Queueable;

    protected array $action;

    protected ?string $subject;

    protected ?string $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Reschedule $reschedule, public readonly string $type)
    {
        $entries = collect(Reschedule::$msgGroups)->map(function ($data) use ($reschedule) {
            return collect($data)->map(fn ($msg) => __($msg, [
                $reschedule->invitee->company->name,
                $reschedule->requestor->company->name,
            ]));
        });

        $subjects = $reschedule->status === 'pending' ? [
            'sender' => 'Appointment Reschedule Request Sent',
            'recipient' => 'Appointment Reschedule Request Received',
            'admin' => 'Appointment Reschedule Request Created',
        ] : [];

        $actions = [
            'sender' => ['link' => '#', 'title' => 'View Reschedule'],
            'recipient' => ['link' => '#', 'title' => 'View Reschedule'],
            'admin' => ['link' => '#', 'title' => 'Review Reschedule'],
        ];

        $this->action = [$actions[$this->type]] ?? [];
        $this->subject = $subjects[$this->type] ?? ucwords("Appointment Reschedule {$reschedule->status}");
        $this->message = $entries[$this->type][$this->reschedule->status] ?? null;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(User $notifiable): array
    {
        return $this->message ? ['mail'] : [];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject($this->subject)
            ->view(['email', 'email-plain'], [
                'subject' => $this->subject,
                'lines' => ["Hello $notifiable->firstname,", $this->message, ...$this->action],
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
