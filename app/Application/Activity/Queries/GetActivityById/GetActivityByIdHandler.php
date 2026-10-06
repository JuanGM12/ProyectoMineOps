<?php

declare(strict_types=1);

namespace App\Application\Activity\Queries\GetActivityById;

use App\Application\Activity\DTOs\ActivityDTO;
use App\Application\Activity\Exceptions\ActivityNotFound;
use App\Domain\Activity\Repositories\ActivityRepository;
use App\Domain\Activity\ValueObjects\ActivityId;

final readonly class GetActivityByIdHandler
{
    public function __construct(private ActivityRepository $activities) {}

    public function handle(GetActivityByIdQuery $query): ActivityDTO
    {
        $activityId = new ActivityId($query->activityId);
        $activity = $this->activities->findById($activityId)
            ?? throw ActivityNotFound::withId($activityId);

        return ActivityDTO::fromDomain($activity);
    }
}
