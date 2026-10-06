<?php

declare(strict_types=1);

namespace App\Application\Activity\Bus;

interface CommandBus
{
    public function dispatch(Command $command): mixed;
}
