<?php

namespace Tests\Feature;

use App\Events\Verified;
use App\Helpers\Url;
use App\Models\User;
use App\Notifications\AccountVerified;
use App\Notifications\SendCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function testCanSendVerificationCode(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $user->sendEmailVerificationNotification();

        Notification::assertSentTo(
            $user,
            function (SendCode $notification, array $channels) use ($user) {
                return $notification->code === $user->email_verify_code;
            }
        );
    }

    public function testCanSendVerifiedNotification(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $user->sendEmailVerificationNotification();

        $this->actingAs($user)->put(
            'api/verify/with-code/email',
            ['code' => $user->email_verify_code]
        );

        Notification::assertSentTo(
            $user,
            AccountVerified::class
        );
    }

    public function testDispatchesVerifiedEvent(): void
    {
        Event::fake();

        $user = User::factory()->unverified()->create();

        $user->sendEmailVerificationNotification();

        $this->actingAs($user)->put(
            'api/verify/with-code/email',
            ['code' => $user->email_verify_code]
        );

        Event::assertDispatched(function (Verified $event) use ($user) {
            return $event->user->is($user);
        });
    }

    public function testDispatchesVerifiedEventFromToken(): void
    {
        Event::fake();

        $user = User::factory()->unverified()->create();

        $user->sendEmailVerificationNotification();

        $this->actingAs($user)->put(
            'api/verify/with-code/email',
            ['code' => Url::base64urlEncode($user->email_verify_code.'|'.md5(time()))]
        );

        Event::assertDispatched(function (Verified $event) use ($user) {
            return $event->user->is($user);
        });
    }

    public function testCanLoadDatabaseNotifications(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $user->notify(new \Tests\Notifications\TestNotifications());

        $response = $this->actingAs($user)->get('api/account/notifications');

        $response->assertOk();
        Notification::assertSentTo($user, \Tests\Notifications\TestNotifications::class);
    }

    public function testCanDeleteDatabaseNotifications(): void
    {
        $user = User::factory()->unverified()->create();

        $user->notify(new \Tests\Notifications\TestNotifications());

        $id = $user->notifications()->first()->id;
        $response = $this->actingAs($user)->delete('api/account/notifications/'.$id);

        $response->assertAccepted();
    }
}
