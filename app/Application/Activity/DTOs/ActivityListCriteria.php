<?php

declare(strict_types=1);

namespace App\Application\Activity\DTOs;

use App\Domain\Activity\Enums\ActivityPriority;
use App\Domain\Activity\Enums\ActivityStatus;
use DateTimeImmutable;

final readonly class ActivityListCriteria
{
    public function __construct(
        public int $page = 1,
        public int $perPage = 15,
        public ?ActivityStatus $status = null,
        public ?ActivityPriority $priority = null,
        public ?string $responsibleId = null,
        public ?DateTimeImmutable $scheduledFrom = null,
        public ?DateTimeImmutable $scheduledTo = null,
        public bool $overdue = false,
        public string $sort = 'created_at',
        public string $direction = 'desc',
    ) {}
}
