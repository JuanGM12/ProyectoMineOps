<?php

declare(strict_types=1);

namespace App\Application\Activity\DTOs;

use App\Domain\Activity\Entities\Activity;

final readonly class ActivityDTO
{
    public function __construct(
        public string $id,
        public string $code,
        public string $title,
        public ?string $description,
        public string $area,
        public string $location,
        public string $responsibleId,
        public string $priority,
        public string $status,
        public string $scheduledDate,
        public ?string $dueDate,
    ) {}

    public static function fromDomain(Activity $activity): self
    {
        return new self(
            id: $activity->id()->value(),
            code: $activity->code()->value(),
            title: $activity->title()->value(),
            description: $activity->description(),
            area: $activity->area(),
            location: $activity->location(),
            responsibleId: $activity->responsibleId()->value(),
            priority: $activity->priority()->value,
            status: $activity->status()->value,
            scheduledDate: $activity->schedule()->scheduledDate()->format('Y-m-d'),
            dueDate: $activity->schedule()->dueDate()?->format('Y-m-d'),
        );
    }
}
