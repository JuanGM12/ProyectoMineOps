<?php

declare(strict_types=1);

namespace App\Domain\Activity\Events;

use DateTimeImmutable;

interface DomainEvent
{
    public function aggregateId(): string;

    public function occurredOn(): DateTimeImmutable;
}
