<?php

declare(strict_types=1);

namespace App\Application\Activity\Commands\CancelActivity;

use App\Application\Activity\DTOs\ActivityDTO;
use App\Application\Activity\Exceptions\ActivityNotFound;
use App\Application\Contracts\UnitOfWork;
use App\Domain\Activity\Repositories\ActivityRepository;
use App\Domain\Activity\ValueObjects\ActivityId;

final readonly class CancelActivityHandler
{
    public function __construct(
        private ActivityRepository $activities,
        private UnitOfWork $unitOfWork,
    ) {}

    public function handle(CancelActivityCommand $command): ActivityDTO
    {
        return $this->unitOfWork->transactional(function () use ($command): ActivityDTO {
            $activityId = new ActivityId($command->activityId);
            $activity = $this->activities->findById($activityId)
                ?? throw ActivityNotFound::withId($activityId);

            $activity->cancel();
            $this->activities->save($activity);

            return ActivityDTO::fromDomain($activity);
        });
    }
}
