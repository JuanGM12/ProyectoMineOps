<?php

declare(strict_types=1);

namespace App\Application\Activity\Queries\GetActivities;

use App\Application\Activity\Contracts\ActivityReadRepository;
use App\Application\Activity\DTOs\ActivityListCriteria;
use App\Application\Activity\DTOs\PaginatedActivitiesDTO;

final readonly class GetActivitiesHandler
{
    public function __construct(private ActivityReadRepository $activities) {}

    public function handle(GetActivitiesQuery $query): PaginatedActivitiesDTO
    {
        return $this->activities->paginate(new ActivityListCriteria(
            page: $query->page,
            perPage: $query->perPage,
            status: $query->status,
            priority: $query->priority,
            responsibleId: $query->responsibleId,
            scheduledFrom: $query->scheduledFrom,
            scheduledTo: $query->scheduledTo,
            overdue: $query->overdue,
            sort: $query->sort,
            direction: $query->direction,
        ));
    }
}
