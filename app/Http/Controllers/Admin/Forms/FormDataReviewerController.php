<?php

namespace App\Http\Controllers\Admin\Forms;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\Form;
use App\Models\FormData;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FormDataReviewerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Form $form, FormData $data)
    {
        @[
            'search' => $search,
        ] = $this->validate($request, [
            'search' => ['nullable', 'string'],
        ]);

        $query = $data->reviewers();
        $query->when($search, fn (Builder $query) => $query->doSearch($search));

        $users = $query->paginate($request->input('limit', 30));

        return (new UserCollection($users))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Form $form, FormData $data)
    {
        @[
            'user_id' => $user_id,
        ] = $this->validate($request, [
            'user_id' => ['required', 'alphanum', 'exists:users,id'],
        ]);

        $data->reviewers()->attach($user_id);
        /** @var \App\Models\User $reviewer */
        $reviewer = $data->reviewers()->find($user_id);
        $reviewer->syncRoles($reviewer->getRoleNames()->merge(['reviewer']));

        return (new UserResource($reviewer))->additional([
            'message' => __(":0 has been added as as reviewer for :1's submission for :2", [
                $reviewer->fullname,
                $data->fullname,
                $form->title,
            ]),
            'status' => 'success',
            'status_code' => HttpStatus::CREATED,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Form $form, FormData $data, User $reviewer)
    {
        $data->reviewers()->detach($reviewer->id);

        return (new UserCollection([]))->additional([
            'message' => __(":0 has been removed as reviewer for :1's submission for :2", [
                $reviewer->fullname,
                $data->fullname,
                $form->title,
            ]),
            'status' => 'success',
            'status_code' => HttpStatus::ACCEPTED,
        ]);
    }
}
