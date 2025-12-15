<?php

namespace App\Traits;

use App\Enums\HttpStatus;
use App\Models\TempUser;
use App\Models\User;
use App\Services\Payment\PaystackProcessor;
use App\Services\Payment\StripeProcessor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait ControllerCanExtend
{
    /**
     * Prefered Payment Processor
     *
     * @var string
     */
    protected $paymentProcessor = 'paystack';

    /**
     * Set the prefered payment processor
     */
    public function payWith(string $paymentProcessor = 'paystack'): static
    {
        $this->paymentProcessor = $paymentProcessor;

        return $this;
    }

    /**
     * Initiate a payment
     *
     * @param  int|float  $amount
     * @param  int|float  $discount
     *
     * @throws ValidationException
     */
    public function makePayment(
        Request $request,
        User|TempUser $user,
        int $amount,
        Model $transactable = null,
        int $discount = 0,
    ): \Illuminate\Support\Collection {
        $fees = 0;

        if ($this->paymentProcessor === 'paystack') {
            $paymentIntent = (new PaystackProcessor($request, $user))->intent($amount - $discount);
        } elseif ($this->paymentProcessor === 'paystack') {
            $paymentIntent = (new StripeProcessor($request, $user))->intent($amount - $discount);
        } else {
            throw ValidationException::withMessages([
                'payment_method' => 'Unknown Payment Method Selected.',
            ]);
        }

        if (isset($paymentIntent['error'])) {
            $user instanceof TempUser && $user->delete();
            abort($paymentIntent['error'], $paymentIntent['message']);
        }

        // Create transaction
        /** @var \App\Models\Transaction */
        $transaction = $user->transactions()->make([
            'request_data' => $request->all(),
            'reference' => $paymentIntent['reference'] ?? $paymentIntent['id'],
            'discount' => $discount,
            'status' => 'pending',
            'method' => $this->paymentProcessor,
            'amount' => $amount,
            'fees' => $fees,
            'due' => ($amount - $discount) + $fees,
        ]);

        if ($transactable) {
            $transaction->transactable_type = $transactable->getMorphClass();
            $transaction->transactable_id = $transactable->id;
        }

        $transaction->save();

        return collect($paymentIntent->toArray());
    }

    /**
     * Verify a payment
     *
     * @throws ValidationException
     */
    public function verifyPayment(
        Request $request,
        User|TempUser $user,
        string $reference,
    ): \Illuminate\Support\Collection {

        if ($this->paymentProcessor === 'paystack') {
            $paymentIntent = (new PaystackProcessor($request, $user))->verify($reference);
        } elseif ($this->paymentProcessor === 'paystack') {
            $paymentIntent = (new StripeProcessor($request, $user))->verify($reference);
        } else {
            throw ValidationException::withMessages([
                'payment_method' => 'Unknown Payment Method Selected.',
            ]);
        }

        /** @var \App\Models\Transaction */
        $transaction = $user->transactions()->where('reference', $reference)->first();

        if (isset($paymentIntent['error'])) {
            if ($transaction) {
                $transaction->status = 'rejected';
                $transaction->save();
            }
            abort(HttpStatus::BAD_REQUEST->value, 'We were unable to verify this transaction.');
        }

        // Update the transaction status
        if ($transaction) {
            $transaction->status = 'complete';
            $transaction->save();
        }

        return collect($paymentIntent->toArray());
    }
}
