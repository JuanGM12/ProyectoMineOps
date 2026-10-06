<?php

declare(strict_types=1);

namespace App\Application\Activity\Exceptions;

use App\Domain\Activity\ValueObjects\ActivityCode;
use RuntimeException;

final class ActivityCodeAlreadyExists extends RuntimeException
{
    public static function withCode(ActivityCode $code): self
    {
        return new self("Ya existe una actividad con el código {$code->value()}.");
    }
}
