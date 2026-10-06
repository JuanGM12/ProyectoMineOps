<?php

declare(strict_types=1);

namespace App\Application\Activity\Bus;

use RuntimeException;

final class QueryHandlerNotFound extends RuntimeException
{
    public static function for(Query $query): self
    {
        $queryClass = $query::class;

        return new self("No existe un Handler registrado para {$queryClass}.");
    }
}
