<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | When syncing roles and permissions using the app:sync-roles command, this
    | model will be considered as the user model and used to associte roles and
    | permissions to the indicated users.
    */
    'user-model' => \App\Models\User::class,
    /*
    |--------------------------------------------------------------------------
    | Super Admin Role
    |--------------------------------------------------------------------------
    |
    | This is the role that would be considered as the super admin role.
    */
    'super-admin-role' => 'super-admin',
    /*
    |--------------------------------------------------------------------------
    | Role List
    |--------------------------------------------------------------------------
    |
    | These are roles that will be made available  to the user.
    | Feel free to add or remove as per your requirements.
    */
    'roles' => [
        'admin',
        'manager',
        'reviewer',
        'super-admin',
    ],
    /*
    |--------------------------------------------------------------------------
    | Elevated Role List
    |--------------------------------------------------------------------------
    |
    | Users with any of the roles listed here are considered to have elevated access
    | Listed roles should already be defined in [roles] above.
    */
    'elevated-roles' => [
        'admin',
        'manager',
        'super-admin',
    ],
    /*
    |--------------------------------------------------------------------------
    | Permission List
    |--------------------------------------------------------------------------
    |
    | These are permissions will be attached to all roles unless they appear in
    | the exclusionlist.
    | Feel free to add or remove as per your requirements.
    */
    'permissions' => [
        'manage-users',
        'manage-admins',
        'configuration',
        'manage-configuration',
        'notifications-temps',

        'users.list',
        'users.create',
        'users.user',
        'users.update',
        'users.delete',

        'form.list',
        'form.show',
        'form.create',
        'form.update',
        'form.delete',
        'form.dashboard',
        'form.dashboard.control',
        'form.reviewers.manage',

        'formfield.list',
        'formfield.show',
        'formfield.create',
        'formfield.update',
        'formfield.delete',

        'formdata.list',
        'formdata.show',
        'formdata.update',
        'formdata.delete',

        'dashboard',
        'content.create',
        'content.update',
        'content.delete',
        'front_content',
        'subscriptions',
        'transactions',

        'spaces',
        'spaces.list',
        'spaces.show',
        'spaces.create',
        'spaces.update',
        'spaces.delete',

        'reservation.list',
        'reservation.show',
        'reservation.create',
        'reservation.update',
        'reservation.delete',

        'appointment.list',
        'appointment.update',
        'appointment.delete',
        'appointment.manage',

        'company.list',
        'company.update',
        'company.delete',
        'company.manage',

        'reschedule.list',
        'reschedule.update',
        'reschedule.delete',
        'reschedule.manage',

        'safe-readonly',
    ],
    /*
    |--------------------------------------------------------------------------
    | Form Specific Role List
    |--------------------------------------------------------------------------
    |
    | These are form specific roles that will be made available to the user.
    | Feel free to add or remove as per your requirements.
    */
    'form_roles' => [
        'form.viewer',
        'form.manager',
        'form.reviewer',
    ],
    /*
    |--------------------------------------------------------------------------
    | Form Permissions
    |--------------------------------------------------------------------------
    |
    | Form Specific Permissions.
    */
    'form_permissions' => [
        'form.list',
        'form.show',
        'form.create',
        'form.update',
        'form.delete',
        'form.dashboard',
        'form.dashboard.control',
        'form.reviewers.manage',

        'formfield.list',
        'formfield.show',
        'formfield.create',
        'formfield.update',
        'formfield.delete',

        'formdata.list',
        'formdata.show',
        'formdata.update',
        'formdata.delete',
    ],
    /*
    |--------------------------------------------------------------------------
    | Form Specific Exclusion List
    |--------------------------------------------------------------------------
    |
    | If there are permisions you do not want to attach to a particlular form role
    | you can add them here using the role name as key.
    */
    'form_exclusions' => [
        'form.manager' => [],
        'form.viewer' => [
            'form.update',
            'form.delete',
            'form.dashboard.control',
            'form.reviewers.manage',
            // ======================
            'formfield.list',
            'formfield.show',
            'formfield.create',
            'formfield.update',
            'formfield.delete',
            //=======================
            'formdata.update',
            'formdata.delete',
        ],
        'form.reviewer' => [
            'form.update',
            'form.delete',
            'form.reviewers.manage',
            // ======================
            'formfield.list',
            'formfield.show',
            'formfield.create',
            'formfield.update',
            'formfield.delete',
            //=======================
            'formdata.update',
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Exclusion List
    |--------------------------------------------------------------------------
    |
    | If there are permisions you do not want to attach to a particlular role
    | you can add them here using the role name as key.
    */
    'exclusions' => [
        'admin' => [
            'safe-readonly',

            'manage-admins',
            'manage-configuration',
        ],
        'manager' => [
            'safe-readonly',

            'users.create',
            'users.update',
            'users.delete',

            'content.create',
            'content.update',
            'content.delete',

            'front_content',

            'formdata.delete',
            'company.delete',
            'reschedule.delete',
            'appointment.delete',

            'manage-admins',
            'configuration',
            'manage-configuration',
        ],
        'reviewer' => [
            'safe-readonly',

            'users.list',
            'users.create',
            'users.user',
            'users.update',
            'users.delete',
            'form.dashboard.control',

            'formdata.delete',
            'form.reviewers.manage',

            'form.create',
            'form.update',
            'form.delete',

            'formfield.create',
            'formfield.update',
            'formfield.delete',

            'content.create',
            'content.update',
            'content.delete',
            'front_content',
            'subscriptions',
            'transactions',
            'configuration',

            'spaces.create',
            'spaces.update',
            'spaces.delete',

            'reservation.create',
            'reservation.update',
            'reservation.delete',

            'manage-admins',
            'manage-users',
            'manage-configuration',

            'reservation.create',
            'reservation.update',
            'reservation.delete',

            'company.update',
            'company.delete',
            'company.manage',

            'reschedule.update',
            'reschedule.delete',
            'reschedule.manage',
            'notifications-temps',
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Admin Roles
    |--------------------------------------------------------------------------
    |
    | Users with any of the following roles are considered admins in general contexts.
    */
    'admin_roles' => [
        'super-admin',
        'admin',
        'manager',
        'reviewer',
    ],
];
