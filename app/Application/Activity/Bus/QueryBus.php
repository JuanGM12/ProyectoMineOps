<?php

declare(strict_types=1);

namespace App\Application\Activity\Bus;

interface QueryBus
{
    public function ask(Query $query): mixed;
}
