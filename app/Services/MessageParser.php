<?php

namespace App\Services;

use App\Helpers\Providers;
use App\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;

class MessageParser
{
    /**
     * The length of the plain message body
     *
     * @var int
     */
    public $length = [];

    /**
     * The corresponding message key from the [messages] config
     *
     * @var string
     */
    public $configKey = '';

    /**
     * The message subject
     *
     * @var string
     */
    public $subject = '';

    /**
     * The message body
     *
     * @var array<int,string|array>
     */
    public $lines = [];

    /**
     * The message body
     *
     * @var string
     */
    public $body = '';

    /**
     * The message body with stripped tags
     *
     * @var string
     */
    public $plainBody = '';

    /**
     * Will be set if config is not found
     *
     * @var bool
     */
    public $notFound = false;

    /**
     * Renderable HTML message string or view name
     *
     * @var \Illuminate\Support\HtmlString|string
     */
    public \Illuminate\Support\HtmlString|string $htmlMessage = 'email';

    /**
     * Renderable plain message string or view name
     *
     * @var \Illuminate\Support\HtmlString|string
     */
    public \Illuminate\Support\HtmlString|string $plainMessage = 'email-plain';

    /**
     * Initialize the class
     *
     * @param  string  $configKey The corresponding message key from the [messages] config
     * @param  mixed[]  $params Exra parameters to pass to the config
     */
    public function __construct(
        string $configKey,
        protected array $params = []
    ) {
        $this->configKey = $configKey;
    }

    /**
     * Parses a message in the messages config and returns
     * It in the required format
     *
     * @param  string  $config
     */
    public function parse(): self
    {
        $params = collect($this->params)
            ->map(fn($val) => $val instanceof Model ? $val->toArray() : $val)
            ->collapse()
            ->filter(fn($item) => is_scalar($item))
            ->all();

        if (count($params) && !isset($this->params['lines'])) {
            $this->params = $params;
        }

        $lines =  isset($this->params['lines']) && count($this->params['lines']) > 0
            ? $this->params['lines']
            : collect(config("messages.{$this->configKey}.lines", []));

        // Parse the message lines
        $lines = $lines->map(function ($line) {
            // If the line is an array (a button) parse it the content also
            if (is_array($line)) {
                return collect($line)->mapWithKeys(function ($val, $key) {
                    return [$key => trans($val, $this->params)];
                })->all();
            }

            // The line should now be return safe
            return is_string($line)
                ? trans($line, $this->params)
                : $line;
        })->merge([config('messages.signature')]);

        $this->lines = $lines->all();

        $this->body = $lines->map(function ($line) {
            if (is_array($line)) {
                return collect($line)->values()->first(fn($val) => filter_var($val, FILTER_VALIDATE_URL), '');
            }

            return $line;
        })->join("\n");

        $this->plainBody = str($this->body)->stripTags()->toString();

        $this->length = str($this->body)->length();

        // Parse the message subject
        $this->subject = trans(config("messages.{$this->configKey}.subject", ''), $this->params);

        if (! config("messages.{$this->configKey}")) {
            $this->notFound = true;
        }

        return $this;
    }

    public function toMail(): MailMessage
    {
        $template = (new NotificationTemplate())->resolveRouteBinding($this->configKey);

        $this->htmlMessage = $template && $template->active
            ? new \Illuminate\Support\HtmlString((string) trans($template->html, $this->params))
            : 'email';

        $this->plainMessage = $template && $template->active
            ? new \Illuminate\Support\HtmlString((string) trans($template->plain, $this->params))
            : 'email-plain';

        if ($template->lines && count((array)$template->lines) > 0) {

            if (!$template->active) {
                $init = new static($this->configKey, $this->params);
                $init->params['lines'] = $template->lines;
                $parse = $init->parse();

                $this->lines = $parse->lines;
                $this->subject = $parse->subject;
            } else {
                $this->lines = $template->lines;
            }
        }

        return (new MailMessage())
            ->subject($this->subject)
            ->view([$this->htmlMessage, $this->plainMessage], [
                'subject' => $this->subject,
                'lines' => $this->lines,
            ]);
    }

    public function toPlain(): string
    {
        $template = (new NotificationTemplate())->resolveRouteBinding($this->configKey);

        $this->plainMessage = $template && $template->active
            ? (string) trans($template->plain, $this->params)
            : $this->plainBody;

        return $this->plainMessage;
    }

    public function toSms(): string
    {
        $template = (new NotificationTemplate())->resolveRouteBinding($this->configKey);

        $smsMessage = $template && $template->active && $template->sms
            ? (string) trans($template->sms, $this->params)
            : $this->plainBody;

        return $smsMessage;
    }

    public function generator(): self
    {
        $template = (new NotificationTemplate())->resolveRouteBinding($this->configKey);

        $this->htmlMessage = $template && $template->active
            ? new \Illuminate\Support\HtmlString((string) trans($template->html, $this->params))
            : 'email';

        $this->plainMessage = $template && $template->active
            ? new \Illuminate\Support\HtmlString((string) trans($template->plain, $this->params))
            : 'email-plain';

        if ($template->lines && count((array)$template->lines) > 0) {
            $this->lines = $template->lines;
        }

        return $this;
    }
}
