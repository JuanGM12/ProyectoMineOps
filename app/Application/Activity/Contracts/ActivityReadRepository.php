<?php

declare(strict_types=1);

namespace App\Application\Activity\Contracts;

use App\Application\Activity\DTOs\ActivityListCriteria;
use App\Application\Activity\DTOs\PaginatedActivitiesDTO;

interface ActivityReadRepository
{
    public function paginate(ActivityListCriteria $criteria): PaginatedActivitiesDTO;
}
