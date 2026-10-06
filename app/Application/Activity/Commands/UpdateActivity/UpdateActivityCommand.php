<?php

declare(strict_types=1);

namespace App\Application\Activity\Commands\UpdateActivity;

use App\Application\Activity\Bus\Command;
use App\Domain\Activity\Enums\ActivityPriority;
use DateTimeImmutable;

final readonly class UpdateActivityCommand implements Command
{
    public function __construct(
        public string $activityId,
        public string $title,
        public ?string $description,
        public string $area,
        public string $location,
        public string $responsibleId,
        public ActivityPriority $priority,
        public DateTimeImmutable $scheduledDate,
        public ?DateTimeImmutable $dueDate,
    ) {}
}
