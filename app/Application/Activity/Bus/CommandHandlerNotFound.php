<?php

declare(strict_types=1);

namespace App\Application\Activity\Bus;

use RuntimeException;

final class CommandHandlerNotFound extends RuntimeException
{
    public static function for(Command $command): self
    {
        $commandClass = $command::class;

        return new self("No existe un Handler registrado para {$commandClass}.");
    }
}
