<?php

namespace V1\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $type = $this->transactable instanceof \App\Models\Portal\Portal
            ? 'portal'
            : 'space';

        $user = $type === 'portal'
            ? app($this->transactable->registration_model)->find($this->user_id)
            : $this->user;

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'reference' => $this->reference,
            'item' => [
                'id' => $this->transactable->id,
                'slug' => $this->transactable->slug,
                'title' => $this->transactable->title ?? $this->transactable->name,
                'name' => $this->transactable->title ?? $this->transactable->name,
                'type' => $type,
            ],
            'amount' => $this->amount,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'date' => $this->created_at ? $this->created_at->format('d M, Y h:i A') : 'N/A',
            'user' => $this->when(! $request->route()->named('user.transactions.index'), new UserResource($user)),
        ];
    }
}
