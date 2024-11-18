<?php

namespace Tests\Unit;

use App\Enums\HttpStatus;
use App\Models\User;
use App\Traits\ControllerCanExtend;
use Illuminate\Foundation\Http\FormRequest;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use ControllerCanExtend;

    protected function setUp(): void
    {
        parent::setUp();

        // $this->markTestSkipped('all tests in this file are invactive for this server configuration!');
    }

    public function testAPaymentTransactionCanRecievePaymentRequestData(): void
    {
        $request_data = ['random' => 'accepted'];
        $request = new FormRequest();
        $request->merge($request_data);

        $user = User::factory()->create();

        $pay = $this->payWith('paystack')->makePayment($request, $user, 1000);

        $verify = $this->payWith('paystack')->verifyPayment($request, $user, $pay['reference'] ?? null);
        $transaction = $user->transactions()->whereReference($pay['reference'])->firstOrFail();

        $this->assertEquals(HttpStatus::OK, $verify['status_code'] ?? null);
        $this->assertEquals($transaction->request_data->toArray(), $request_data);
    }
}
