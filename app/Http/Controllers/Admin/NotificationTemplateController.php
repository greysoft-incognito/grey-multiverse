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
        \App\Enums\Permission::NOTIFICATIONS_TEMPS->authorize();

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
        \App\Enums\Permission::NOTIFICATIONS_TEMPS->authorize();

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
        \App\Enums\Permission::NOTIFICATIONS_TEMPS->authorize();

        if ($template->id === -1) {
            unset($template->id);
        }

        $template->sms = $request->sms;
        $template->args = $request->args;
        $template->html = $request->boolean('active') ? $request->html : null;
        $template->lines = $request->lines;
        $template->plain = $request->plain;
        $template->active = $request->boolean('active');
        $template->subject = $request->subject;
        $template->footnote = $request->footnote;
        $template->copyright = $request->copyright;
        $template->save();

        return (new NotificationTemplateResource($template))->additional([
            'message' => __('Notification template successfully saved.'),
            'status' => 'success',
            'statusCode' => HttpStatus::OK,
        ])->response()->setStatusCode(HttpStatus::OK->value);
    }

    public function destroy(Request $request, NotificationTemplate $template)
    {
        $template->delete();

        return (new NotificationTemplateResource($template->resolveRouteBinding($template->key)))->additional([
            'message' => __('Notification template has been reset to default state.'),
            'status' => 'success',
            'statusCode' => HttpStatus::OK,
        ])->response()->setStatusCode(HttpStatus::OK->value);
    }
}
