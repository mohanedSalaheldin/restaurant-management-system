<?php

namespace App\Enum;

enum UserRole: string
{
    //
    case ADMIN = 'admin';
    case WAITER = 'waiter';
    case CASHIER = 'cashier';
    case KITCHEN_STAFF = 'kitchen_staff';

    public function redirectRoute(): string
    {
        return match ($this) {
            self::ADMIN => 'admin.dashboard',
            self::WAITER => 'waiter.dashboard',
            self::CASHIER => 'cashier.dashboard',
            self::KITCHEN_STAFF => 'kitchen.view',
        };
    }
}
