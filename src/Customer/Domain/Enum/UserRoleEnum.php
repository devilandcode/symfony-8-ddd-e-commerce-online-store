<?php

declare(strict_types=1);

namespace App\Customer\Domain\Enum;

enum UserRoleEnum: string
{
    case ADMIN = 'admin';
    case USER = 'user';
}
