<?php

namespace App\Enums;

/**
 * Admin Permissions.
 */
enum Permission: string
{
    case APPROVE_TRANSACTIONS = 'approve-transactions';
    case MANAGE_USERS = 'manage-users';
    case MANAGE_ADMINS = 'manage-admins';
    case CONFIGURATION = 'configuration';
    case MANAGE_CONFIGURATION = 'manage-configuration';
    case NOTIFICATIONS_TEMPS = 'notifications-temps';

    case USERS_LIST = 'users.list';
    case USERS_CREATE = 'users.create';
    case USERS_USER = 'users.user';
    case USERS_UPDATE = 'users.update';
    case USERS_DELETE = 'users.delete';

    case FORM_LIST = 'form.list';
    case FORM_SHOW = 'form.show';
    case FORM_CREATE = 'form.create';
    case FORM_UPDATE = 'form.update';
    case FORM_DELETE = 'form.delete';
    case FORM_REVIEWERS_MANAGE = 'form.reviewers.manage';

    case FORMFIELD_LIST = 'formfield.list';
    case FORMFIELD_SHOW = 'formfield.show';
    case FORMFIELD_CREATE = 'formfield.create';
    case FORMFIELD_UPDATE = 'formfield.update';
    case FORMFIELD_DELETE = 'formfield.delete';

    case FORMDATA_STATS = 'form.dashboard';
    case FORMDATA_LIST = 'formdata.list';
    case FORMDATA_SHOW = 'formdata.show';
    case FORMDATA_UPDATE = 'formdata.update';
    case FORMDATA_DELETE = 'formdata.delete';

    case DASHBOARD = 'dashboard';
    case CONTENT_CREATE = 'content.create';
    case CONTENT_UPDATE = 'content.update';
    case CONTENT_DELETE = 'content.delete';
    case FRONT_CONTENT = 'front_content';
    case SUBSCRIPTIONS = 'subscriptions';
    case TRANSACTIONS = 'transactions';

    case SPACES = 'spaces';
    case SPACES_LIST = 'spaces.list';
    case SPACES_SHOW = 'spaces.show';
    case SPACES_CREATE = 'spaces.create';
    case SPACES_UPDATE = 'spaces.update';
    case SPACES_DELETE = 'spaces.delete';

    case RESERVATION_LIST = 'reservation.list';
    case RESERVATION_SHOW = 'reservation.show';
    case RESERVATION_CREATE = 'reservation.create';
    case RESERVATION_UPDATE = 'reservation.update';
    case RESERVATION_DELETE = 'reservation.delete';

    case APPOINTMENT_LIST = 'appointment.list';
    case APPOINTMENT_UPDATE = 'appointment.update';
    case APPOINTMENT_DELETE = 'appointment.delete';
    case APPOINTMENT_MANAGE = 'appointment.manage';

    case COMPANY_LIST = 'company.list';
    case COMPANY_UPDATE = 'company.update';
    case COMPANY_DELETE = 'company.delete';
    case COMPANY_MANAGE = 'company.manage';

    case RESCHEDULE_LIST = 'reschedule.list';
    case RESCHEDULE_UPDATE = 'reschedule.update';
    case RESCHEDULE_DELETE = 'reschedule.delete';
    case RESCHEDULE_MANAGE = 'reschedule.manage';

    /**
     * Check and authorise the admin on the current permission
     *
     * @return void
     */
    public function authorize()
    {
        \App\Helpers\Access::authorize($this);
    }

    /**
     * Check and authorise the admin on muliptle permissions
     *
     * @param  self|array<int,self>  $permission
     * @return void
     */
    public static function authorizeAll(self|array $permission)
    {
        \App\Helpers\Access::authorize($permission);
    }
}
