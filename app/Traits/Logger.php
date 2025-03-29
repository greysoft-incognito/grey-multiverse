<?php

namespace App\Traits;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;

trait Logger
{
    /**
     * Log an action for the model
     *
     * @param string $action
     * @param string|null $description
     * @param array|null $properties
     * @return Log
     */
    public function logActivity(string $action, ?string $description = null, ?array $properties = null): Log
    {
        $user = Auth::user();

        return Log::create([
            'loggable_id' => $this->id,
            'loggable_type' => get_class($this),
            'user_id' => $user?->id,
            'user_type' => $user ? get_class($user) : null,
            'action' => $action,
            'description' => $description,
            'properties' => $properties
        ]);
    }

    /**
     * Get all logs for this model
     */
    public function logs()
    {
        return $this->morphMany(Log::class, 'loggable');
    }

    /**
     * Boot the trait and add model event listeners
     *
     * @param array<int,'created','updated','deleted'> $loggable_actions
     * @return void
     */
    public static function bootLogger($loggable_actions = ['created', 'updated', 'deleted'])
    {
        in_array('created', $loggable_actions) && static::created(function ($model) {
            /** @var \Illuminate\Database\Eloquent\Model|self $model */

            $model->logActivity(
                'created',
                'Created new ' . class_basename($model)
            );
        });


        in_array('updated', $loggable_actions) && static::updated(function ($model) {
            /** @var \Illuminate\Database\Eloquent\Model|self $model */

            if ($model->isDirty()) {
                // Get the changed attributes (dirty)
                $changes = $model->getChanges();

                unset($changes['updated_at']); // Exclude updated_at from changes

                if (!empty($changes)) {
                    // Get the original values for only the changed attributes
                    $original = array_intersect_key($model->getOriginal(), $changes);

                    $model->logActivity(
                        'updated',
                        'Updated ' . class_basename($model),
                        [
                            'old' => $original, // Original values of changed attributes
                            'new' => $changes   // New values of changed attributes
                        ]
                    );
                }
            }
        });

        in_array('deleted', $loggable_actions) && static::deleted(function ($model) {
            /** @var \Illuminate\Database\Eloquent\Model|self $model */

            $model->logActivity(
                'deleted',
                'Deleted ' . class_basename($model)
            );
        });
    }
}
