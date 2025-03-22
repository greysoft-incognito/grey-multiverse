<?php

namespace App\Http\Controllers\Admin;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        @[
            'type' => $type,
            'search' => $search,
        ] = $this->validate($request, [
            'type' => ['nullable', 'string', 'in:user,admin'],
            'search' => ['nullable', 'string'],
        ]);

        $query = User::query();

        $query
            ->when($type, fn ($q) => $q->isAdmin($type === 'admin'))
            ->when($search, fn ($q) => $q->doSearch($search));

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
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $valid = $this->validate($request, [
            'image' => ['nullable', 'image'],
            'name' => ['required_without:firstname', 'string', 'max:255'],
            'email' => ['required_without:phone', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => 'required_without:email|string|max:255|unique:users,phone',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'firstname' => ['nullable', 'string', 'max:255'],
            'laststname' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:user,admin,...'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['required', 'string', Rule::in(config('permission-defs.roles'))],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['required', 'string', Rule::in(config('permission-defs.permissions'))],
        ], [
            'name.required_without' => 'Please enter the user\'s fullname.',
        ], [
            'email' => 'email address',
            'phone' => 'phone number',
        ]);

        $valid['firstname'] = str($request->get('name'))->explode(' ')->first(null, $request->firstname);
        $valid['lastname'] = str($request->get('name'))->explode(' ')->last(fn ($n) => $n !== $valid['firstname'], $request->lastname);

        /** @var \App\Models\User $user */
        $user = User::create($valid);

        if (isset($valid['roles'])) {
            $user->syncRoles($valid['roles']);
        }

        if (isset($valid['permissions'])) {
            $user->syncPermissions($valid['permissions']);
        }

        return (new UserResource($user))->additional([
            'message' => 'User created successfully',
            'status' => 'success',
            'status_code' => HttpStatus::CREATED,
        ])->response()->setStatusCode(HttpStatus::CREATED->value);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return (new UserResource($user))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::CREATED,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        \Artisan::call('optimize:clear');
        \Artisan::call('app:sync-roles');
        \Artisan::call('optimize:clear');
        $valid = $this->validate($request, [
            'image' => ['nullable', 'image'],
            'name' => ['required_without:firstname', 'string', 'max:255'],
            'email' => ['required_without:phone', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => 'required_without:email|string|max:255|unique:users,phone,'.$user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'firstname' => ['nullable', 'string', 'max:255'],
            'laststname' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:user,admin,...'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['required', 'string', Rule::in(config('permission-defs.roles'))],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['required', 'string', Rule::in(config('permission-defs.permissions'))],
        ], [
            'name.required_without' => 'Please enter the user\'s fullname.',
        ], [
            'email' => 'email address',
            'phone' => 'phone number',
        ]);

        $valid['firstname'] = str($request->name)->explode(' ')->first(null, $request->firstname);
        $valid['lastname'] = str($request->name)->explode(' ')->last(fn ($n) => $n !== $valid['firstname'], $request->lastname);

        $user->update($valid);

        if (isset($valid['roles'])) {
            $user->syncRoles($valid['roles']);
        }

        if (isset($valid['permissions'])) {
            $user->syncPermissions($valid['permissions']);
        }

        return (new UserResource($user))->additional([
            'message' => 'User update successfull',
            'status' => 'success',
            'status_code' => HttpStatus::ACCEPTED,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED->value);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $ids = $request->input('items', [$id]);
        User::whereIn('id', $ids)->delete();

        return (new UserCollection([]))->additional([
            'message' => (count($ids) > 1 ? count($ids).' users' : 'User').' deleted successfully',
            'status' => 'success',
            'status_code' => HttpStatus::ACCEPTED,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED->value);
    }
}
