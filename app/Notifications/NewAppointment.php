<?php

namespace App\Notifications;

use App\Models\BizMatch\Appointment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAppointment extends Notification
{
    use Queueable;

    protected array $action;

    protected ?string $subject;

    protected ?string $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Appointment $appointment, public readonly string $type)
    {
        $entries = collect(Appointment::$msgGroups)->map(function ($data) use ($appointment) {
            return collect($data)->map(fn ($msg) => __($msg, [
                $appointment->invitee->company->name,
                $appointment->requestor->company->name,
            ]));
        });

        $subjects = $appointment->status === 'pending' ? [
            'sender' => 'New Appointment Request Sent',
            'recipient' => 'New Appointment Request Received',
            'admin' => 'New Appointment Request Created',
        ] : [];

        $actions = [
            'sender' => ['link' => '#', 'title' => 'View Appointment'],
            'recipient' => ['link' => '#', 'title' => 'View Appointment'],
            'admin' => ['link' => '#', 'title' => 'Review Appointment'],
        ];

        $this->action = [$actions[$this->type]] ?? [];
        $this->subject = $subjects[$this->type] ?? ucwords("Appointment {$appointment->status}");
        $this->message = $entries[$this->type][$this->appointment->status] ?? null;
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
    public function toMail(User $notifiable): ?MailMessage
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