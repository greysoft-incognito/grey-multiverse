<?php

namespace V1\Http\Controllers\Portal;

use App\Models\Portal\Portal;
use Illuminate\Http\Request;
use V1\Http\Controllers\Controller;
use V1\Http\Resources\Portal\BlogCollection;
use V1\Http\Resources\Portal\BlogResource;
use V1\Services\HttpStatus;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Portal $portal)
    {
        $query = $portal->blogs();

        // Search and filter columns
        if ($request->search) {
            $query->where(function ($query) use ($request) {
                $query->where('title', 'like', "%$request->search%");
                $query->orWhereFullText('content', $request->search);
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

        $posts = $query->paginate($request->get('limit', 30))->withQueryString();

        return (new BlogCollection($posts))->additional(array_merge([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ], $request->search ? ['total_results' => $query->count()] : []));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Portal $portal, $id)
    {
        $blog = $portal->blogs()->where('id', $id)->orWhere('slug', $id)->firstOrFail();

        return (new BlogResource($blog))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }
}
