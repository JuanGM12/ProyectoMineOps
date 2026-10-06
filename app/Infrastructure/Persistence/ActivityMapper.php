<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Activity\Entities\Activity;
use App\Domain\Activity\Enums\ActivityPriority;
use App\Domain\Activity\Enums\ActivityStatus;
use App\Domain\Activity\ValueObjects\ActivityCode;
use App\Domain\Activity\ValueObjects\ActivityId;
use App\Domain\Activity\ValueObjects\ActivitySchedule;
use App\Domain\Activity\ValueObjects\ActivityTitle;
use App\Domain\Activity\ValueObjects\ResponsibleId;
use App\Infrastructure\Persistence\Eloquent\Models\ActivityModel;
use DateTimeImmutable;

final class ActivityMapper
{
    public static function toDomain(ActivityModel $model): Activity
    {
        return Activity::reconstitute(
            id: new ActivityId((string) $model->getKey()),
            code: new ActivityCode((string) $model->code),
            title: new ActivityTitle((string) $model->title),
            description: $model->description,
            area: (string) $model->area,
            location: (string) $model->location,
            responsibleId: new ResponsibleId((string) $model->responsible_id),
            priority: ActivityPriority::from((string) $model->priority),
            status: ActivityStatus::from((string) $model->status),
            schedule: new ActivitySchedule(
                new DateTimeImmutable($model->scheduled_date->format('Y-m-d')),
                $model->due_date === null ? null : new DateTimeImmutable($model->due_date->format('Y-m-d')),
            ),
        );
    }

    public static function toModel(Activity $activity, ?ActivityModel $model = null): ActivityModel
    {
        $model ??= new ActivityModel;

        $model->fill([
            'id' => $activity->id()->value(),
            'code' => $activity->code()->value(),
            'title' => $activity->title()->value(),
            'description' => $activity->description(),
            'area' => $activity->area(),
            'location' => $activity->location(),
            'responsible_id' => $activity->responsibleId()->value(),
            'priority' => $activity->priority()->value,
            'status' => $activity->status()->value,
            'scheduled_date' => $activity->schedule()->scheduledDate()->format('Y-m-d'),
            'due_date' => $activity->schedule()->dueDate()?->format('Y-m-d'),
        ]);

        return $model;
    }
}
