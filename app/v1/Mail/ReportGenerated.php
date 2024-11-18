<?php

namespace V1\Mail;

use App\Models\Form;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
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
        $message = [
            'cta' => ['link' => route('download.formdata', [$timestamp, $encoded, $this->batch]), 'title' => 'Download Report'],
            'message_line1' => __('Your bi-weekly report report for :0 is ready!', [$this->title ?? $this->form->name]),
            'message_line2' => 'For security and privacy concerns this link expires in 10 hours and is only usable once.',
            'message_line3' => 'If you have any concerns please mail <a href="mailto:hi@greysoft.ng">hi@greysoft.ng</a> for support.',
            'close_greeting' => __('Regards, <br/>:0', ['Greysoft Technologies']),
        ];

        return $this->view('email', $message)
            ->text('email-plain')
            ->subject(__(':0 Report is ready', [$this->title ?? $this->form->name]));
    }
}
