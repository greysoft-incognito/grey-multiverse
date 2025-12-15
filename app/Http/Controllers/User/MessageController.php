<?php

namespace App\Http\Controllers\User;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessageCollection;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $conversation_type, string $conversation_id)
    {
        $conversation = $this->conversation($conversation_type, $conversation_id);

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $conversation->messages();

        $messages = $query->latest('created_at')->paginate($request->integer('limit', 50));

        return (new MessageCollection($messages))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ])->response()->setStatusCode(HttpStatus::OK->value);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $conversation_type, string $conversation_id)
    {
        $conversation = $this->conversation($conversation_type, $conversation_id);

        @[
            'text' => $text,
        ] = $this->validate($request, [
            'text' => ['required_without:attachements', 'string', 'min:1', 'max:1000'],
            'attachements' => ['required_without:text', 'file', 'mimes:jpg,png,jpeg,gif,pdf,doc,docx'],
        ]);

        $message = $conversation->sendMessage($request->user()->id, $text);

        return (new MessageResource($message))->additional([
            'message' => __('Your message has been sent.'),
            'status' => 'success',
            'status_code' => HttpStatus::CREATED,
        ])->response()->setStatusCode(HttpStatus::CREATED->value);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $conversation_type, string $conversation_id, string $message_id)
    {
        $conversation = $this->conversation($conversation_type, $conversation_id);

        /** @var Message $message */
        $message = $conversation->messages()->findOrFail($message_id);

        return (new MessageResource($message))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ])->response()->setStatusCode(HttpStatus::OK->value);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $conversation_type, string $conversation_id, string $message_id)
    {
        $conversation = $this->conversation($conversation_type, $conversation_id);

        @[
            'text' => $text,
        ] = $this->validate($request, [
            'text' => ['required_without:attachements', 'string', 'min:1', 'max:1000'],
            'attachements' => ['required_without:text', 'file', 'mimes:jpg,png,jpeg,gif,pdf,doc,docx'],
        ]);

        /** @var Message $message */
        $message = $conversation->messages()->findOrFail($message_id);
        $message->text = $text;
        $message->save();

        return (new MessageResource($message))->additional([
            'message' => __('Your message has been updated.'),
            'status' => 'success',
            'status_code' => HttpStatus::ACCEPTED,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED->value);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $conversation_type, string $conversation_id, string $message_id)
    {
        $conversation = $this->conversation($conversation_type, $conversation_id);

        /** @var Message $message */
        $message = $conversation->messages()->findOrFail($message_id);
        $message->delete();

        return (new MessageResource($message))->additional([
            'message' => __('Your message has been deleted.'),
            'status' => 'success',
            'status_code' => HttpStatus::ACCEPTED,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED->value);
    }

    /**
     * Get the active conversation
     *
     * @return \App\Models\BizMatch\Appointment|\App\Models\BizMatch\Conversation
     */
    protected function conversation(string $conversation_type, string $conversation_id)
    {
        $className = str(str($conversation_type)->explode('-')->map(fn ($v) => str($v)->singular()->studly())->join('\\'))
            ->prepend('\\App\\Models\\')
            ->toString();

        abort_if(! class_exists($className), 404, 'Conversation not found.');

        /** @var \Illuminate\Database\Eloquent\Builder $baseQuery */
        $baseQuery = $className::query();

        return $baseQuery->findOrFail($conversation_id);
    }
}
