<?php

namespace V1\Http\Controllers\Manage;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use V1\Http\Controllers\Controller;
use V1\Http\Resources\User\UserCollection;
use V1\Http\Resources\User\UserResource;
use V1\Services\HttpStatus;

class UsersController extends Controller
{
    /**
     * Display a listing of all user.
     *
     * @param  string  $type
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $limit = '15', $role = 'user')
    {
        \Gate::authorize('usable', 'users.list');
        $query = User::query();

        // Search and filter columns
        if ($request->search) {
            $query->where(function ($query) use ($request) {
                $query->Where('lastname', 'like', "%$request->search%")
                    ->orWhere('firstname', 'like', "%$request->search%")
                    ->orWhere('email', 'like', "%$request->search%");
            });
        }

        // Reorder Columns
        if ($request->order && is_array($request->order)) {
            foreach ($request->order as $key => $dir) {
                if ($dir === 'desc') {
                    $query->orderByDesc($key ?? 'id');
                } else {
                    $query->orderBy($key ?? 'id');
                }
            }
        }

        $users = ($limit <= 0 || $limit === 'all') ? $query->get() : $query->paginate($limit);

        return (new UserCollection($users))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => $users->isEmpty() ? 'info' : 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }

    public function show($id)
    {
        \Gate::authorize('usable', 'users.user');
        $user = User::findOrFail($id);

        return (new UserResource($user))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }

    public function store(Request $request)
    {
        \Gate::authorize('usable', 'users.create');
        $user = new User;

        Validator::make($request->all(), [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id ?? '')],
            'phone' => ['sometimes', 'string', 'max:255', Rule::unique('users')->ignore($user->id ?? '')],
            'privileges' => ['sometimes', 'array'],
        ], [])->validate();

        $user = $user ?? new User;

        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->phone = $request->phone;
        if ($request->privileges) {
            $prev = is_array($request->privileges) ? $request->privileges : explode(',', $request->privileges);
            $user->privileges = $prev;
        }
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return (new UserResource($user))->additional([
            'message' => Str::of($user->fullname)->append(' Has been updated!'),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }

    public function update(Request $request, $id = '')
    {
        \Gate::authorize('usable', 'users.update');
        $user = User::findOrFail($id);

        Validator::make($request->all(), [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id ?? '')],
            'phone' => ['sometimes', 'string', 'max:255', Rule::unique('users')->ignore($user->id ?? '')],
        ], [])->validate();

        $user = $user ?? new User;

        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->phone = $request->phone;

        if ($request->privileges) {
            $prev = is_array($request->privileges) ? $request->privileges : explode(',', $request->privileges);
            $user->privileges = $prev;
        }
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return (new UserResource($user))->additional([
            'message' => Str::of($user->fullname)->append(' Has been updated!'),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }

    /**
     * Remove the specified user from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id = '')
    {
        \Gate::authorize('usable', 'users.delete');
        // Delete multiple users
        if ($request->users) {
            $count = User::whereIn('id', $request->users)->get()->map(function ($user) {
                // Delete Transactions
                // ***
                // Delete User
                return $user->delete();

                return false;
            })->filter(fn ($i) => $i !== false)->count();

            return $this->buildResponse([
                'message' => "{$count} users have been deleted.",
                'status' => 'success',
                'response_code' => HttpStatus::ACCEPTED,
            ]);
        } else {
            $user = User::whereId($id)->firstOrFail();
        }

        // Delete single user
        if ($user) {
            $user->delete();

            return $this->buildResponse([
                'message' => "{$user->fullname} has been deleted.",
                'status' => 'success',
                'response_code' => HttpStatus::ACCEPTED,
            ]);
        }
    }
}
