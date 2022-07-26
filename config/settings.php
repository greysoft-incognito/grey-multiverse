<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Site specific Configuration
    |--------------------------------------------------------------------------
    |
    | These settings determine how the site is to be run or managed
    |
    */
    'site_name' => 'Grey Multiverse',
    'currency_symbol' => '₦',
    'currency' => 'NGN',
    'use_queue' => true,
    'prefered_notification_channels' => ['mail'],
    'keep_successful_queue_logs' => true,
    'default_user_about' => 'Only Business Minded!',
    'strict_mode' => false, // Setting to true will prevent the Vcard Engine from generating Vcards with repeated content
    'slack_debug' => false,
    'slack_logger' => false,
    'force_https' => true,
    'verify_email' => false,
    'verify_phone' => false,
    'token_lifespan' => 1,
    'frontend_link' => 'http://localhost:8080',
    'payment_verify_url' => env('PAYMENT_VERIFY_URL', 'http://localhost:8080/payment/verify'),
    'default_banner' => 'http://127.0.0.1:8000/media/images/829496537_368214255.jpg',
    'auth_banner' => 'http://127.0.0.1:8000/media/images/773106123_1122399045.jpg',
    'paystack_public_key' => env('PAYSTACK_PUBLIC_KEY', 'pk_'),
    'trx_prefix' => 'TRX-',
    'contact_address' => '31 Gwari Avenue, Barnawa, Kaduna',

    'system' => [
        'paystack' => [
            'secret_key' => env('PAYSTACK_SECRET_KEY', 'sk_'),
        ],
        'ipinfo' => [
            'access_token' => env('IPINFO_ACCESS_TOKEN'),
        ],
    ],
    /*
    |---------------------------------------------------------------------------------
    | Message templates
    |---------------------------------------------------------------------------------
    | Variables include {username}, {name}, {firstname}, {lastname}, {site_name}, {message}, {reserved}
    |
     */
    'messages' => [
        'variables' => 'Available Variables: {username}, {name}, {firstname}, {lastname}, {site_name}, {message}, {reserved}. (Some variables may not apply to some actions)',
        'greeting' => 'Hello {username},',
        'mailing_list_sub' => 'You are receiving this email because you recently subscribed to our mailing list, what this means is that you will get every information about {site_name} before anyone else does. Thanks for your support!',
        'mailing_list_sub_admin' => '{name} has just joined the mailing list.',
        'mailing_list_exit' => 'We are sad to see you go, but we are okay with it, we hope to see you again.',
        'mailing_list_exit_admin' => '{name} has just left the mailing list.',
        'contact_message' => 'Thank you for contacting us, we will look in to your query and respond if we find the need to do so.',
        'contact_message_admin' => "{name} has just sent a message: \n {message}",
    ],
];
