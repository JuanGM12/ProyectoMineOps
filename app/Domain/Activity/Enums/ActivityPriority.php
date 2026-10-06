<?php

declare(strict_types=1);

namespace App\Domain\Activity\Enums;

enum ActivityPriority: string
{
    case LOW = 'LOW';
    case MEDIUM = 'MEDIUM';
    case HIGH = 'HIGH';
    case CRITICAL = 'CRITICAL';
}
