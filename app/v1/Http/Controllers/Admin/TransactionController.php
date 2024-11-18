<?php

namespace V1\Http\Controllers\Admin;

use App\Models\Transaction as Transaction;
use Illuminate\Http\Request;
use V1\Http\Controllers\Controller;
use V1\Http\Resources\User\TransactionCollection;
use V1\Http\Resources\User\TransactionResource;
use V1\Services\HttpStatus;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $status = null)
    {
        $limit = $request->limit ?? 15;
        $query = Transaction::query()->orderByDesc('id');

        if ($status) {
            $query->where('status', $status);
        }

        $transaction = $query->paginate($limit);

        return (new TransactionCollection($transaction))->additional([
            'message' => 'OK',
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function invoice(Request $request, $reference)
    {
        return $this->buildResponse([
            'message' => 'OK',
            'status' => 'success',
            'status_code' => HttpStatus::OK,
            ...new TransactionCollection(Transaction::whereReference($reference)->get()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        return (new TransactionResource($transaction))->additional([
            'message' => 'OK',
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
