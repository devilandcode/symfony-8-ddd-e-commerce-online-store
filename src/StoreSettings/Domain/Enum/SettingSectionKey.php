<?php

declare(strict_types=1);

namespace App\StoreSettings\Domain\Enum;

enum SettingSectionKey: string
{
    case CONTACTS = 'contacts';
    case CONTENT = 'content';
    case METRICS = 'metrics';
}
