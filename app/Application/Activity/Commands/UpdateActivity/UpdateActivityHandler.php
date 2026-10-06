<?php

declare(strict_types=1);

namespace App\Application\Activity\Commands\UpdateActivity;

use App\Application\Activity\DTOs\ActivityDTO;
use App\Application\Activity\Exceptions\ActivityNotFound;
use App\Application\Contracts\UnitOfWork;
use App\Domain\Activity\Repositories\ActivityRepository;
use App\Domain\Activity\ValueObjects\ActivityId;
use App\Domain\Activity\ValueObjects\ActivitySchedule;
use App\Domain\Activity\ValueObjects\ActivityTitle;
use App\Domain\Activity\ValueObjects\ResponsibleId;

final readonly class UpdateActivityHandler
{
    public function __construct(
        private ActivityRepository $activities,
        private UnitOfWork $unitOfWork,
    ) {}

    public function handle(UpdateActivityCommand $command): ActivityDTO
    {
        return $this->unitOfWork->transactional(function () use ($command): ActivityDTO {
            $activityId = new ActivityId($command->activityId);
            $activity = $this->activities->findById($activityId)
                ?? throw ActivityNotFound::withId($activityId);

            $activity->rename(new ActivityTitle($command->title));
            $activity->updateDetails($command->description, $command->area, $command->location);
            $activity->assignResponsible(new ResponsibleId($command->responsibleId));
            $activity->revisePlanning(
                $command->priority,
                new ActivitySchedule($command->scheduledDate, $command->dueDate),
            );

            $this->activities->save($activity);

            return ActivityDTO::fromDomain($activity);
        });
    }
}
