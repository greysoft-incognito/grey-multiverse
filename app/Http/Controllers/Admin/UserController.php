<?php

namespace App\Http\Controllers\Admin;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query();

        $query->when($request->has('type'), function (Builder $query) use ($request) {
            $query->where('type', $request->input('type'));
        });
        $query->when($request->has('search'), function (Builder $query) use ($request) {
            $query->where('firstname', 'like', '%'.$request->input('search').'%');
            $query->orWhere('lastname', 'like', '%'.$request->input('search').'%');
            $query->orWhereRaw(
                "LOWER(CONCAT_WS(' ', firstname, lastname)) like ?",
                ['%'.mb_strtolower($request->input('search')).'%']
            );
        });

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
        $valid = $this->validate($request, [
            'image' => ['nullable', 'image'],
            'name' => ['required_without:firstname', 'string', 'max:255'],
            'email' => ['required_without:phone', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => 'required_without:email|string|max:255|unique:users,phone,'.$user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'firstname' => ['nullable', 'string', 'max:255'],
            'laststname' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:user,admin,...'],
            'roles' => ['array', 'in:'.implode(',', config('permission-defs.admin_roles', []))],
            'permissions' => ['array', 'in:'.implode(',', config('permission-defs.permissions', []))],
        ], [
            'name.required_without' => 'Please enter the user\'s fullname.',
        ], [
            'email' => 'email address',
            'phone' => 'phone number',
        ]);

        $valid['firstname'] = str($request->name)->explode(' ')->first(null, $request->firstname);
        $valid['lastname'] = str($request->name)->explode(' ')->last(fn ($n) => $n !== $valid['firstname'], $request->lastname);

        $user->update($valid);

        $user->syncRoles($valid['roles'] ?? []);

        $user->syncPermissions($valid['permissions'] ?? []);

        return (new UserResource($user))->additional([
            'message' => 'User update successfull',
            'status' => 'success',
            'status_code' => HttpStatus::ACCEPTED,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED->value);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return (new UserCollection([]))->additional([
            'message' => 'User deleted successfully',
            'status' => 'success',
            'status_code' => HttpStatus::ACCEPTED,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED->value);
    }
}
