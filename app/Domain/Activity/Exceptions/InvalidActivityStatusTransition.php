<?php

declare(strict_types=1);

namespace App\Domain\Activity\Exceptions;

use App\Domain\Activity\Enums\ActivityStatus;

final class InvalidActivityStatusTransition extends DomainException
{
    public static function fromTo(ActivityStatus $current, ActivityStatus $target): self
    {
        return new self("No se puede cambiar una actividad de {$current->value} a {$target->value}.");
    }
}
