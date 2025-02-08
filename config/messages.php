<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Signature
    |--------------------------------------------------------------------------
    |
    | This is required and will be attached to the end of every message
    |
    */
    'signature' => 'Regards,<br />',

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    |
    | The message body is made up of lines, each line
    | represents a new line in the message sent, inline html is also supported
    | If a line is required to be a button it should be an array in the
    | following format: ['link' => 'https://tech4all.greysoft.ng/login', 'title' => 'Get Started', 'color' => '#fff']
    | the color property is optional.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | SendCode messages
    |--------------------------------------------------------------------------
    |
    | These are messages sent during account or request verification.
    |
    */
    'send_code::reset' => [
        'subject' => 'Reset your :app_name password.',
        'lines' => [
            'Hello :firstname,',
            'You are receiving this email because we received a password reset request for your account on :app_name.',
            'Use the code or link below to recover your account.',
            '<h3 style="text-align: center;">:code</h3>',
            [
                'link' => ':app_url/reset/password?token=:token',
                'title' => 'Reset Password',
            ],
            'This password reset code will expire in :duration.',
            'If you did not request a password reset, no further action is required.',
        ],
    ],
    'send_code::verify' => [
        'subject' => 'Verify your account on :app_name.',
        'lines' => [
            'Hello :firstname,',
            'You are receiving this email because you created an account on <b>:app_name</b> and we needed to verify that you own this :label. <br />Use the code or link below to verify your :label.',
            '<h3 style="text-align: center;">:code</h3>',
            [
                'link' => ':app_url/account/verify/:type?token=:token',
                'title' => 'Verify Account',
            ],
            'This verification code will expire in :duration.',
            'If you do not recognize this activity, no further action is required as the associated account will be deleted in few days if left unverified.',
        ],
    ],
    'send_code::otp' => [
        'subject' => 'Your One Time Password.',
        'lines' => [
            'Use the code below to verify your request.',
            '<h3 style="text-align: center;">:code</h3>',
            'This OTP will expire in :duration.',
            'If you do not recognize this request no further action is required or you can take steps to secure your account.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SendVerified messages
    |--------------------------------------------------------------------------
    |
    | These are messages sent after an account is verified.
    |
    */
    'send_verified' => [
        'subject' => 'Welcome to the :app_name community.',
        'lines' => [
            'Hello :firstname,',
            'Your :app_name account :label has been verified sucessfully and we want to use this opportunity to welcome you to our community.',
            [
                'link' => ':app_url/login',
                'title' => 'Get Started',
            ],
        ],
    ],
    'send_verified:sms' => [
        'subject' => 'Welcome to the :app_name community.',
        'lines' => [
            'Hello :firstname,',
            'Your :app_name account :label has been verified sucessfully, welcome to our community.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Other messages
    |--------------------------------------------------------------------------
    |
    |
    */
    'email_verification' => [
        'subject' => 'Please verify your :label.',
        'lines' => [
            'Hello :fullname,',
            'You initiated an account opening proccess at :app_name, please use the code below to complete your request',
            '<h3 style="text-align: center;">:code</h3>',
            'If you need any further assistance please reachout to support.',
        ],
    ],
    'welcome' => [
        'subject' => ':firstname, welcome to :app_name.',
        'lines' => [
            'Hello :fullname,',
            "We are happy to have you onboard :app_name, your registration was successfull and we can't wait to see what you do next.",
            "If you do need any assistance please don't fail to reachout to one of our numerous support channels.",
        ],
    ],
    'send_report' => [
        'subject' => ':form_name, Report is ready.',
        'lines' => [
            'Your :period report report for :form_name is ready!',
            [
                'link' => ':link',
                'title' => 'Download Report',
            ],
            'For security and privacy concerns this link expires in :ttl and is only usable once',
            'If you have any concerns please mail <a href="mailto:hi@greysoft.ng">hi@greysoft.ng</a> for support.'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact and mailing list messages
    |--------------------------------------------------------------------------
    |
    |
    */
    'mailing_list_sub' => [
        'subject' => ':name, welcome to :app_name newsletter.',
        'lines' => [
            'Hello :name,',
            'You are receiving this email because you recently subscribed to our newsletter',
            'what this means is that you will get every information about :app_name before anyone else does. Thanks for your support.',
        ],
    ],
    'mailing_list_sub_admin' => [
        'subject' => ':name joined newsletter.',
        'lines' => [
            'Hello Admin,',
            ':name has just joined the mailing list.',
        ],
    ],
    'mailing_list_exit' => [
        'subject' => 'Goodbye :name.',
        'lines' => [
            'Hello :name,',
            'We are sad to see you go, but we are okay with it, we hope to see you again soon.',
        ],
    ],
    'mailing_list_exit_admin' => [
        'subject' => 'Say goodbye to :name.',
        'lines' => [
            'Hello Admin,',
            ':name has just left the mailing list.',
        ],
    ],
    'contact_message' => [
        'subject' => 'Message Recieved.',
        'lines' => [
            'Hello :name,',
            'Thank you for contacting us, we will look in to your query and reachout to you if we find the need to do so.',
        ],
    ],
    'contact_message_admin' => [
        'subject' => 'Message Recieved.',
        'lines' => [
            'Hello :name,',
            'name: has just sent a message:',
            ':message',
        ],
    ],
];
