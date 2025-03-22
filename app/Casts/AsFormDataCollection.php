<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use ToneflixCode\DbConfig\Models\Fileable;
use ToneflixCode\LaravelFileable\Facades\Media;

class AsFormDataCollection implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @return \Illuminate\Contracts\Database\Eloquent\CastsAttributes<\Illuminate\Support\Collection<array-key, mixed>, iterable>
     */
    public static function castUsing(array $arguments)
    {
        return new class($arguments) implements CastsAttributes
        {
            public function __construct(protected array $arguments)
            {
            }

            public function get($model, $key, $value, $attributes)
            {
                if (! isset($attributes[$key])) {
                    return;
                }

                $data = Json::decode($attributes[$key]);

                $collectionClass = $this->arguments[0] ?? Collection::class;

                if (! is_a($collectionClass, Collection::class, true)) {
                    throw new InvalidArgumentException('The provided class must extend ['.Collection::class.'].');
                }

                $output = is_array($data) ? new $collectionClass($data) : null;

                if ($output) {
                    $fileFields = $model->form->fields->where('type', 'file');
                    if ($fileFields->isNotEmpty()) {
                        $file_list = [];
                        foreach ($fileFields as $field) {
                            if (isset($output[$field->name])) {
                                $file_ids = is_array($output[$field->name])
                                    ? $output[$field->name]
                                    : Json::decode($output[$field->name]);

                                if (! empty($file_ids)) {
                                    /** @var \Illuminate\Database\Eloquent\Collection<Fileable> $file */
                                    $file = Fileable::whereIn('id', $file_ids)->get();
                                    $file_list = $file->map(fn ($f) => $f->file_url.'?fn='.$f->file)->toArray();

                                    if (count($file_list) === 1) {
                                        $file_list = $file_list[0];
                                    }

                                    $output[$field->name] = $file_list;
                                }
                            }
                        }
                    }
                }

                return $output;
            }

            public function set($model, $key, $value, $attributes)
            {
                return [$key => Json::encode($this->processData($value, $model))];
            }

            public function processData(array|\Illuminate\Support\Collection $data, Model $model): array
            {
                if (is_array($data)) {
                    $data = collect($data);
                }

                $data = $data->map(function ($data, $key) use ($model) {
                    if ($data instanceof UploadedFile) {
                        return $this->doUpload($data, $key, $model);
                    }

                    return $data;
                });

                return $data->toArray();
            }

            /**
             * Upload a file as configuration value
             *
             * @param  UploadedFile|UploadedFile[]  $files
             * @return string
             */
            public function doUpload(UploadedFile|array $files, string $key, Model $model)
            {
                if (! method_exists($model, 'files')) {
                    return '';
                }

                $value = DB::transaction(function () use ($files, $key, $model) {
                    $value = [];
                    try {
                        $model->files()->whereJsonContains('meta->key', $key)->delete();
                        if (is_array($files)) {
                            $value = collect($files)->map(function (UploadedFile $item, int $i) use ($key, $model) {
                                $file = $model->files()->make();
                                $file->meta = ['type' => 'form-data', 'key' => $key];
                                $file->file = Media::save('form-data', $item, $model->files[$i]->file ?? null);
                                $file->fileable_collection = 'form-data';
                                $file->saveQuietly();

                                return $file->id;
                            })->toArray();
                        } else {
                            $file = $model->files()->make();
                            $file->meta = ['type' => 'form-data', 'key' => $key];
                            $file->file = Media::save('form-data', $files, $model->files[0]->file ?? null);
                            $file->fileable_collection = 'form-data';
                            $file->saveQuietly();
                            $value = [$file->id];

                            return $value;
                        }
                    } catch (\Throwable $th) {
                        throw ValidationException::withMessages([
                            $key => $th->getMessage(),
                        ]);
                    }
                });

                return $value;
            }
        };
    }

    /**
     * Specify the collection for the cast.
     *
     * @param  class-string  $class
     * @return string
     */
    public static function using($class)
    {
        return static::class.':'.$class;
    }
}
