<?php

namespace V1\Mail;

use App\Helpers\Providers;
use App\Models\Form;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\SerializesModels;

class ReportGenerated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Form $form, $batch = null, $title = null)
    {
        $this->form = $form;
        $this->batch = $batch;
        $this->title = $title;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $timestamp = CarbonImmutable::now()->timestamp;
        $encoded = base64_encode("download.formdata/$timestamp/{$this->form->id}");

        $message = Providers::messageParser(
            'send_report',
            [
                'form_name' => $this->title ?? $this->form->name,
                'period' => 'daily',
                'link' => route('download.formdata', [$timestamp, $encoded, $this->batch]),
                'ttl' => '10 hours',
                'app_name' => Providers::config('app_name'),
            ]
        );

        return (new MailMessage())
            ->subject($message->subject)
            ->view(['email', 'email-plain'], [
                'subject' => $message->subject,
                'lines' => $message->lines,
            ]);
    }
}