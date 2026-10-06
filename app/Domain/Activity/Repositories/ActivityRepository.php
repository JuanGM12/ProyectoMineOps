<?php

declare(strict_types=1);

namespace App\Domain\Activity\Repositories;

use App\Domain\Activity\Entities\Activity;
use App\Domain\Activity\ValueObjects\ActivityCode;
use App\Domain\Activity\ValueObjects\ActivityId;

interface ActivityRepository
{
    public function save(Activity $activity): void;

    /** @return list<Activity> */
    public function findAll(): array;

    public function findById(ActivityId $id): ?Activity;

    public function findByCode(ActivityCode $code): ?Activity;

    public function remove(Activity $activity): void;
}
