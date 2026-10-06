<?php

declare(strict_types=1);

namespace App\Domain\Activity\Exceptions;

final class InvalidActivitySchedule extends DomainException
{
    public static function dueDateBeforeScheduledDate(): self
    {
        return new self('La fecha límite no puede ser anterior a la fecha programada.');
    }

    public static function criticalActivityRequiresDueDate(): self
    {
        return new self('Una actividad crítica debe tener fecha límite.');
    }
}
