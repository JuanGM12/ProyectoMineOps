<?php

declare(strict_types=1);

namespace App\Application\Activity\Queries\GetActivities;

use App\Application\Activity\DTOs\ActivityDTO;
use App\Domain\Activity\Entities\Activity;
use App\Domain\Activity\Repositories\ActivityRepository;

final readonly class GetActivitiesHandler
{
    public function __construct(private ActivityRepository $activities) {}

    /** @return list<ActivityDTO> */
    public function handle(GetActivitiesQuery $query): array
    {
        return array_map(
            static fn (Activity $activity): ActivityDTO => ActivityDTO::fromDomain($activity),
            $this->activities->findAll(),
        );
    }
}
