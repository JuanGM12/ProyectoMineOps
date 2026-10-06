<?php

declare(strict_types=1);

namespace App\Domain\Activity\Enums;

enum ActivityStatus: string
{
    case PENDING = 'PENDING';
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';
}
