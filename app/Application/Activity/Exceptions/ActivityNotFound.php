<?php

declare(strict_types=1);

namespace App\Application\Activity\Exceptions;

use App\Domain\Activity\ValueObjects\ActivityId;
use RuntimeException;

final class ActivityNotFound extends RuntimeException
{
    public static function withId(ActivityId $activityId): self
    {
        return new self("No existe una actividad con id {$activityId->value()}.");
    }
}
