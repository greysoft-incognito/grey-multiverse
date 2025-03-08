<?php

namespace App\Models;

use App\Helpers\Providers;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * @property int $id;
 * @property string $key The message template key;
 * @property string $sms The sms representation of the notification;
 * @property string $html The html representation of the notification;
 * @property string $plain The plain text representation of the notification;
 * @property string $subject The message subject;
 * @property array<string, string> $args;
 * @property bool $active If the template is active;
 * @property \Carbon\Carbon $created_at;
 * @property \Carbon\Carbon $updated_at;
 */
class NotificationTemplate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'key',
        'sms',
        'html',
        'args',
        'lines',
        'plain',
        'active',
        'subject',
        'allowed',
        'footnote',
        'copyright',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'args' => '[]',
        'lines' => '[]',
        'allowed' => '[]',
        'active' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts()
    {
        return [
            'args' => \Illuminate\Database\Eloquent\Casts\AsCollection::class,
            'lines' => \Illuminate\Database\Eloquent\Casts\AsCollection::class,
            'allowed' => \Illuminate\Database\Eloquent\Casts\AsCollection::class,
            'active' => 'boolean',
        ];
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  string|null  $field
     * @return self|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        try {
            return $this->where('id', $value)
                ->orWhere('key', $value)
                ->firstOrFail();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $th) {
            return self::buildDefault($value, true);
        }
    }

    /**
     * Get defaults from settings
     *
     * @return Collection<int, static>
     */
    public static function loadDefaults(): Collection
    {
        return new Collection(collect(config('messages'))->map(
            fn($_, $key) => self::buildDefault($key)
        )->filter(fn($_, $key) => $key !== 'signature')->values());
    }

    /**
     * Get defaults from settings
     *
     * @return Collection<int, static>
     */
    public static function buildDefault(string $key, bool $strict = false): self
    {
        $parsed = Providers::messageParser($key);
        $allowed = config("messages.$key.allowed", ['html', 'plain', 'sms']);

        if ($parsed->notFound && $strict) {
            throw (new ModelNotFoundException('Error Processing Request', 1))->setModel(new static());
        }

        preg_match_all('/:(\w+)/', $parsed->body, $args);

        $html = (new MailMessage())
            ->view(['email', 'email-plain'], [
                'lines' => $parsed->lines,
                'subject' => $parsed->subject,
            ])->render();

        $template = new static([
            'id' => -1,
            'key' => $key,
            'sms' => $parsed->plainBody,
            'html' => $html,
            'lines' => $parsed->lines,
            'plain' => $parsed->plainBody,
            'subject' => $parsed->subject,
            'footnote' => $parsed->meta['footnote'] ?? config('messages.footnote', ''),
            'copyright' => $parsed->meta['copyright'] ?? config('messages.copyright', ''),
            'args' => collect([
                'firstname',
                'lastname',
                'fullname',
                'email',
                'phone',
                'app_name',
                'app_url',
            ])->merge($args[1])->unique(),
            'active' => true,
            'allowed' => $allowed,
        ]);

        $template->id = -1;

        return $template;
    }

    public function parsed(): Attribute
    {
        return new Attribute(fn() => Providers::messageParser($this->key));
    }

    public function html(): Attribute
    {
        return new Attribute(
            get: function ($value) {
                if ($value) {
                    return $value;
                }

                return (new MailMessage())
                    ->view(['email', 'email-plain'], [
                    'lines' => $this->parsed->lines,
                    'subject' => $this->parsed->subject,
                    ])->render();
            },
            set: fn($val) => $val,
        );
    }

    public function subject(): Attribute
    {
        return new Attribute(fn($val) => $val ?: $this->parsed->subject);
    }

    public function footnote(): Attribute
    {
        return new Attribute(
            get: fn($val) => $val ?: config('messages.footnote', ''),
            set: fn($val) => $val,
        );
    }

    public function copyright(): Attribute
    {
        return new Attribute(
            get: fn($val) => $val ?: config('messages.copyright', ''),
            set: fn($val) => $val,
        );
    }
}
