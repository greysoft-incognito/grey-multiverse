<?php

namespace V1\Http\Controllers\Admin;

use App\Models\Space;
use Illuminate\Http\Request;
use V1\Http\Controllers\Controller;
use V1\Http\Resources\SpaceCollection;
use V1\Http\Resources\SpaceResource;
use V1\Services\HttpStatus;

class SpacesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('can-do', ['spaces.list']);
        $query = Space::query();
        // Search and filter columns
        if ($request->search) {
            $query->where(function ($query) use ($request) {
                $query->where('name', 'like', "%$request->search%");
                $query->orWhereFullText('info', $request->search);
                $query->orWherePrice($request->search);
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

        $spaces = $query->paginate($request->get('limit', 30));

        return (new SpaceCollection($spaces))->additional(array_merge([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ], $request->search ? ['total_results' => $query->count()] : []));
    }

    public function store(Request $request)
    {
        $this->authorize('can-do', ['spaces.create']);
        $request->validate([
            'name' => 'required|string|max:255',
            'info' => 'required|string',
            'price' => 'required|numeric',
            'size' => 'sometimes|string',
            'color' => 'sometimes|string',
            'image' => 'sometimes|image|mimes:png,jpg,jpeg|max:2048',
            'custom_data' => 'sometimes|array',
            'max_occupants' => 'required|numeric|min:1',
        ]);

        $space = new Space();
        $space->size = $request->size ?? '0';
        $space->color = $request->color;
        $space->name = $request->name;
        $space->info = $request->info;
        $space->price = $request->price;
        $space->data = $request->custom_data ?? [];
        $space->save();

        return (new SpaceResource($space))->additional([
            'message' => __('Space created successfully'),
            'status' => 'success',
            'status_code' => HttpStatus::CREATED,
        ])->response()->setStatusCode(HttpStatus::CREATED);
    }

    public function show(Request $request, Space $space)
    {
        $this->authorize('can-do', ['spaces.show']);

        return (new SpaceResource($space))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }

    public function update(Request $request, Space $space)
    {
        $this->authorize('can-do', ['spaces.update']);
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'info' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'size' => 'sometimes|string',
            'image' => 'sometimes|image|mimes:png,jpg,jpeg|max:2048',
            'custom_data' => 'sometimes|array',
            'max_occupants' => 'sometimes|numeric|min:1',
        ]);

        $space->size = $request->size ?? $space->size ?? '0';
        $space->name = $request->name ?? $space->name;
        $space->info = $request->info ?? $space->info;
        $space->price = $request->price ?? $space->price;
        $space->data = $request->custom_data ?? $space->data ?? [];
        $space->save();

        return (new SpaceResource($space))->additional([
            'message' => __('Space updated successfully'),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id = null)
    {
        $this->authorize('can-do', ['spaces.delete']);
        if ($request->items) {
            $count = collect($request->items)->map(function ($item) {
                $item = Space::find($item);
                if ($item) {
                    return $item->delete();
                }

                return false;
            })->filter(fn ($i) => $i !== false)->count();

            return $this->buildResponse([
                'message' => "{$count} spaces have been deleted.",
                'status' => 'success',
                'status_code' => HttpStatus::OK,
            ]);
        } else {
            $item = Space::findOrFail($id);
            if ($item) {
                $item->delete();

                return $this->buildResponse([
                    'message' => "{$item->name} has been deleted.",
                    'status' => 'success',
                    'status_code' => HttpStatus::ACCEPTED,
                ]);
            }
        }
    }
}
