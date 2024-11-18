<?php

namespace V1\Http\Controllers;

use App\Models\Portal\Portal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use V1\Http\Resources\User\UserResource;
use V1\Services\HttpStatus;
use V1\Traits\Meta;
use Yabacon\Paystack;
use Yabacon\Paystack\Exception\ApiException;

class PaymentController extends Controller
{
    use Meta;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Initialize a paystack transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'type' => ['required', 'string'],
            'portal_id' => ['required_if:type,portal'],
            'learning_path' => ['nullable', 'exists:learning_paths,id'],
            'items' => ['required_if:type,cart_checkout', 'array'],
        ]);

        $user = Auth::user();
        $code = HttpStatus::BAD_REQUEST;
        $due = 0;

        try {
            $reference = config('settings.trx_prefix', 'TRX-').$this->generate_string(20, 3);
            if ($request->type === 'portal') {
                $portal = Portal::findOrFail($request->portal_id);
                $transactions = $portal->transactions();

                if ($request->learning_path && $portal->regForm->learningPaths) {
                    $learning_path = $portal->regForm->learningPaths()->findOrFail($request->learning_path);
                    $reg_fee = $learning_path->price;
                } else {
                    $reg_fee = $portal->reg_fee;
                }

                $due = ceil($request->installment === 1
                    ? $reg_fee
                    : ($request->installment === (1 / 2)
                        ? $reg_fee / 2
                        : ($request->installment === (1 / 3)
                            ? $reg_fee / 3
                            : $reg_fee / 4
                        )
                    ));

                $transactions->create([
                    'user_id' => Auth::id(),
                    'reference' => $reference,
                    'method' => 'Paystack',
                    'status' => 'pending',
                    'amount' => $due,
                    'due' => $due,
                    'data' => [
                        'learning_path' => $request->learning_path ?? null,
                        'installment' => $request->installment,
                        'total' => $reg_fee,
                        'signature' => md5($reg_fee.$user->email.time()),
                    ],
                ]);
            }

            $paystack = new Paystack(env('PAYSTACK_SECRET_KEY'));
            $real_due = round($due * 100, 2);

            // Dont initialize paystack for inline transaction
            if ($request->inline) {
                $tranx = [
                    'data' => ['reference' => $reference],
                ];
                $real_due = $due;
            } else {
                $tranx = $paystack->transaction->initialize([
                    'amount' => $real_due,       // in kobo
                    'email' => $user->email,     // unique to customers
                    'reference' => $reference,   // unique to transactions
                    'callback_url' => $request->get('redirect',
                        config('settings.frontend_link')
                            ? config('settings.frontend_link').'/payment/verify'
                            : config('settings.payment_verify_url', route('payment.paystack.verify'))
                    ),
                ]);
                $real_due = $due;
            }

            $code = 200;

            return $this->buildResponse([
                'message' => $msg ?? HttpStatus::message(HttpStatus::OK),
                'status' => 'success',
                'status_code' => $code ?? HttpStatus::OK, //202
                'payload' => $tranx ?? [],
                'transaction' => $transaction ?? [],
                'amount' => $real_due,
                'refresh' => ['user' => new UserResource($request->user()->refresh())],
            ]);
        } catch (ApiException|\InvalidArgumentException|\ErrorException $e) {
            return $this->buildResponse([
                'message' => $e->getMessage(),
                'status' => 'error',
                'status_code' => $e instanceof ApiException ? HttpStatus::BAD_REQUEST : HttpStatus::SERVER_ERROR,
                'payload' => $e instanceof ApiException ? $e->getResponseObject() : [],
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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

    /**
     * Verify the paystack payment.
     *
     * @param  string  $action
     * @return \Illuminate\Http\Response
     */
    public function paystackVerify(Request $request)
    {
        $type = 'company';
        $status_info = null;
        $process = [
            'message' => 'Invalid Transaction.',
            'status' => 'error',
            'status_code' => HttpStatus::BAD_REQUEST,
        ];

        if (! $request->reference) {
            $process['message'] = 'No reference supplied';
        }

        try {
            $paystack = new Paystack(env('PAYSTACK_SECRET_KEY'));
            $tranx = $paystack->transaction->verify([
                'reference' => $request->reference,   // unique to transactions
            ]);

            $transaction = Transaction::where('reference', $request->reference)->where('status', 'pending')->firstOrFail();
            $transactable = $transaction->transactable;

            if ($transactable instanceof Portal) {
                $process = [
                    'message' => 'Transaction Complete.',
                    'status' => 'success',
                    'status_code' => HttpStatus::ACCEPTED,
                ];
                $type = 'portal';
                $status_info = [
                    'message' => __('Congratulations on your successfull enrolment for :0', [
                        $transactable->name,
                    ]),
                    'info' => __('You can now access the portal and start learning, meanwhile we will reach out to you with more information after we review your entry.'),
                ];
                $transaction->status = 'paid';
                $transaction->save();
            }
        } catch (ApiException|\InvalidArgumentException|\ErrorException $e) {
            $payload = $e instanceof ApiException ? $e->getResponseObject() : [];
            Log::error($e->getMessage(), ['url' => url()->full(), 'request' => $request->all()]);

            return $this->buildResponse([
                'message' => $e->getMessage(),
                'status' => 'error',
                'status_code' => HttpStatus::UNPROCESSABLE_ENTITY,
                'payload' => $payload,
            ]);
        }

        return $this->buildResponse(array_merge($process, [
            'payload' => $tranx ?? [],
            'type' => $type,
            $type => $transactable,
        ]), $status_info ? ['status_info' => $status_info] : null);
    }

    /**
     * Delete a transaction and related models
     * The most appropriate place to use this is when a user cancels a transaction without
     * completing payments, although there are limitless use cases.
     *
     * @return void
     */
    public function terminateTransaction(Request $request)
    {
        $deleted = false;
        if ($transaction = Transaction::whereReference($request->reference)->where('user_id', Auth::id())->first()) {
            $transaction->delete();
            $deleted = true;
        }

        return $this->buildResponse([
            'message' => $deleted
                ? "Transaction with reference: {$request->reference} successfully deleted."
                : 'Transaction not found',
            'status' => ! $deleted ? 'info' : 'success',
            'response_code' => 200,
        ]);
    }
}
