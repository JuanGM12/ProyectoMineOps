<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Activity\Contracts\ActivityReadRepository;
use App\Application\Activity\DTOs\ActivityDTO;
use App\Application\Activity\DTOs\ActivityListCriteria;
use App\Application\Activity\DTOs\PaginatedActivitiesDTO;
use App\Domain\Activity\Enums\ActivityStatus;
use App\Infrastructure\Persistence\Eloquent\Models\ActivityModel;
use Illuminate\Database\Eloquent\Builder;

final class EloquentActivityReadRepository implements ActivityReadRepository
{
    private const SORTABLE_COLUMNS = [
        'created_at',
        'scheduled_date',
        'due_date',
        'title',
        'priority',
        'status',
    ];

    public function paginate(ActivityListCriteria $criteria): PaginatedActivitiesDTO
    {
        $query = ActivityModel::query()
            ->when($criteria->status, fn (Builder $query, $status) => $query->where('status', $status->value))
            ->when($criteria->priority, fn (Builder $query, $priority) => $query->where('priority', $priority->value))
            ->when($criteria->responsibleId, fn (Builder $query, string $responsibleId) => $query->where('responsible_id', $responsibleId))
            ->when($criteria->scheduledFrom, fn (Builder $query, $date) => $query->whereDate('scheduled_date', '>=', $date->format('Y-m-d')))
            ->when($criteria->scheduledTo, fn (Builder $query, $date) => $query->whereDate('scheduled_date', '<=', $date->format('Y-m-d')))
            ->when($criteria->overdue, fn (Builder $query) => $query
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', today()->toDateString())
                ->whereIn('status', [ActivityStatus::PENDING->value, ActivityStatus::IN_PROGRESS->value]));

        $sort = in_array($criteria->sort, self::SORTABLE_COLUMNS, true) ? $criteria->sort : 'created_at';
        $direction = $criteria->direction === 'asc' ? 'asc' : 'desc';
        $paginator = $query
            ->orderBy($sort, $direction)
            ->orderBy('id', $direction)
            ->paginate($criteria->perPage, ['*'], 'page', $criteria->page);

        return new PaginatedActivitiesDTO(
            items: $paginator->getCollection()
                ->map(static fn (ActivityModel $model): ActivityDTO => self::toDTO($model))
                ->values()
                ->all(),
            total: $paginator->total(),
            currentPage: $paginator->currentPage(),
            perPage: $paginator->perPage(),
            lastPage: $paginator->lastPage(),
        );
    }

    private static function toDTO(ActivityModel $model): ActivityDTO
    {
        return new ActivityDTO(
            id: (string) $model->getKey(),
            code: (string) $model->code,
            title: (string) $model->title,
            description: $model->description,
            area: (string) $model->area,
            location: (string) $model->location,
            responsibleId: (string) $model->responsible_id,
            priority: (string) $model->priority,
            status: (string) $model->status,
            scheduledDate: $model->scheduled_date->format('Y-m-d'),
            dueDate: $model->due_date?->format('Y-m-d'),
        );
    }
}
