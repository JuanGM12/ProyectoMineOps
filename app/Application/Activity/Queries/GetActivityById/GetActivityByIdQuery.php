<?php

declare(strict_types=1);

namespace App\Application\Activity\Queries\GetActivityById;

use App\Application\Activity\Bus\Query;

final readonly class GetActivityByIdQuery implements Query
{
    public function __construct(public string $activityId) {}
}
