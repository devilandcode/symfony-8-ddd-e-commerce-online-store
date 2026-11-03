<?php

namespace App\Customer\Domain\Enum;

enum UserStatusEnum: string
{
    case ACTIVE = 'active';
    case WAITING = 'waiting';
    case BLOCKED = 'blocked';
}