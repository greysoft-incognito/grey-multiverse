<?php

namespace V1\Models\Portal;

use App\Models\Form;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use ToneflixCode\LaravelFileable\Traits\Fileable;

class Portal extends Model
{
    use Fileable, HasFactory;

    protected $casts = [
        'allow_registration' => 'boolean',
        'socials' => 'array',
        'footer_groups' => 'array',
    ];

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('id', $value)
            ->orWhere('slug', $value)
            ->firstOrFail();
    }

    public function registerFileable()
    {
        $this->fileableLoader([
            'logo' => 'logo',
            'favicon' => 'logo',
            'banner' => 'banner',
        ]);
    }

    /**
     * Get all of the blogs for the Portal
     */
    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class);
    }

    /**
     * Get all of the pages for the Portal
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function learningPaths(): MorphMany
    {
        return $this->morphMany(LearningPath::class, 'learnable');
    }

    /**
     * Get all of the pages for the Portal
     */
    public function pages(): HasMany
    {
        return $this->hasMany(PortalPage::class);
    }

    /**
     * Get all of the forms for the Portal
     */
    public function forms(): HasMany
    {
        return $this->hasMany(Form::class);
    }

    /**
     * Get the registration form for the Portal
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function regForm(): HasOne
    {
        return $this->hasOne(Form::class, 'portal_id', 'id')->where('id', $this->reg_form_id ?? '---');
    }

    /**
     * Get all of the transactions for the Company
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'transactable');
    }

    public function footerGroups(): Attribute
    {
        return new Attribute(
            get: fn ($value) => collect(json_decode($value))->map(function ($value) {
                return [
                    'value' => $value,
                    'label' => str($value)->ucfirst()->replace(['_', '-'], ' ')->toString(),
                ];
            }),
            set: fn ($value) => collect($value)->map(function ($value) {
                return str($value['value'] ?? $value ?? '')->slug();
            }),
        );
    }

    public function footerPages(): Attribute
    {
        return new Attribute(
            get: (function () {
                $groups = $this->footer_groups->map(function ($group) {
                    return collect($group)->only('value');
                })->flatten();

                $groupItems = $this->pages()->whereIn('footer_group', $groups)->where('in_footer', true)->limit(12)->get();

                return $groups->map(function ($group) use ($groupItems) {
                    return [
                        'value' => $group,
                        'label' => str($group)->ucfirst()->replace(['_', '-'], ' ')->toString(),
                        'pages' => $groupItems->where('footer_group', $group)->where('in_footer', true)->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'slug' => $item->slug,
                                'title' => $item->index ? 'Home' : $item->title,
                                'index' => $item->index,
                                'footer_group' => $item->footer_group,
                            ];
                        })->values(),
                    ];
                });
            }),
        );
    }

    public function navbarPages(): Attribute
    {
        return new Attribute(
            get: (function () {
                return $this->pages()->where('in_navbar', true)->limit(6)->orderByDesc('index')->get()->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'slug' => $item->slug,
                        'title' => $item->index ? 'Home' : $item->title,
                        'index' => $item->index,
                    ];
                })->values();
            }),
        );
    }
}
