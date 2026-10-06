<?php

declare(strict_types=1);

namespace App\Application\Activity\Commands\StartActivity;

use App\Application\Activity\DTOs\ActivityDTO;
use App\Application\Activity\Exceptions\ActivityNotFound;
use App\Application\Contracts\UnitOfWork;
use App\Domain\Activity\Repositories\ActivityRepository;
use App\Domain\Activity\ValueObjects\ActivityId;

final readonly class StartActivityHandler
{
    public function __construct(
        private ActivityRepository $activities,
        private UnitOfWork $unitOfWork,
    ) {}

    public function handle(StartActivityCommand $command): ActivityDTO
    {
        return $this->unitOfWork->transactional(function () use ($command): ActivityDTO {
            $activityId = new ActivityId($command->activityId);
            $activity = $this->activities->findById($activityId)
                ?? throw ActivityNotFound::withId($activityId);

            $activity->start();
            $this->activities->save($activity);

            return ActivityDTO::fromDomain($activity);
        });
    }
}
