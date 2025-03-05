<?php

namespace App\Http\Controllers\Admin;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationTemplateCollection;
use App\Http\Resources\NotificationTemplateResource;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;

class NotificationTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(/*Request $request*/)
    {
        $query = NotificationTemplate::query();

        $templates = $query->latest()->get();

        if ($templates->count() < 1) {
            $templates = NotificationTemplate::loadDefaults();
        }

        return (new NotificationTemplateCollection($templates))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'statusCode' => HttpStatus::OK,
        ])->response()->setStatusCode(HttpStatus::OK->value);
    }

    /**
     * Display the specified resource.
     */
    public function show(NotificationTemplate $template)
    {
        return (new NotificationTemplateResource($template))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'statusCode' => HttpStatus::OK,
        ])->response()->setStatusCode(HttpStatus::OK->value);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NotificationTemplate $template)
    {
        if ($template->id === -1) {
            unset($template->id);
        }

        $template->args = $request->args;
        $template->html = $request->html;
        $template->plain = $request->plain;
        $template->active = $request->boolean('active');
        $template->subject = $request->subject;
        $template->save();

        return (new NotificationTemplateResource($template))->additional([
            'message' => __('Notification template successfully saved.'),
            'status' => 'success',
            'statusCode' => HttpStatus::OK,
        ])->response()->setStatusCode(HttpStatus::OK->value);
    }
}