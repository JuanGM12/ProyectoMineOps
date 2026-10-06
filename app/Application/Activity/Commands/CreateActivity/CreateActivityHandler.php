<?php

declare(strict_types=1);

namespace App\Application\Activity\Commands\CreateActivity;

use App\Application\Activity\DTOs\ActivityDTO;
use App\Application\Contracts\UnitOfWork;
use App\Domain\Activity\Entities\Activity;
use App\Domain\Activity\Repositories\ActivityRepository;
use App\Domain\Activity\ValueObjects\ActivityCode;
use App\Domain\Activity\ValueObjects\ActivityId;
use App\Domain\Activity\ValueObjects\ActivitySchedule;
use App\Domain\Activity\ValueObjects\ActivityTitle;
use App\Domain\Activity\ValueObjects\ResponsibleId;

final readonly class CreateActivityHandler
{
    public function __construct(
        private ActivityRepository $activities,
        private UnitOfWork $unitOfWork,
    ) {}

    public function handle(CreateActivityCommand $command): ActivityDTO
    {
        return $this->unitOfWork->transactional(function () use ($command): ActivityDTO {
            $activity = Activity::create(
                ActivityId::generate(),
                new ActivityCode($command->code),
                new ActivityTitle($command->title),
                $command->description,
                $command->area,
                $command->location,
                new ResponsibleId($command->responsibleId),
                $command->priority,
                new ActivitySchedule($command->scheduledDate, $command->dueDate),
            );

            $this->activities->save($activity);

            return ActivityDTO::fromDomain($activity);
        });
    }
}
