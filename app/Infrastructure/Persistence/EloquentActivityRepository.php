<?php
declare(strict_types=1);
namespace App\Infrastructure\Persistence;
use App\Domain\Activity\Entities\Activity;
use App\Domain\Activity\Repositories\ActivityRepository;
use App\Domain\Activity\ValueObjects\ActivityCode;
use App\Domain\Activity\ValueObjects\ActivityId;
use App\Models\ActivityModel;
final class EloquentActivityRepository implements ActivityRepository
{
    public function save(Activity $activity): void { ActivityMapper::toModel($activity, ActivityModel::query()->find($activity->id()->value()))->save(); }
    /** @return list<Activity> */
    public function findAll(): array
    {
        return ActivityModel::query()->orderBy('id')->get()
            ->map(static fn (ActivityModel $model): Activity => ActivityMapper::toDomain($model))->all();
    }
    public function findById(ActivityId $id): ?Activity
    {
        $model = ActivityModel::query()->find($id->value());
        return $model === null ? null : ActivityMapper::toDomain($model);
    }
    public function findByCode(ActivityCode $code): ?Activity
    {
        $model = ActivityModel::query()->where('code', $code->value())->first();
        return $model === null ? null : ActivityMapper::toDomain($model);
    }
    public function remove(Activity $activity): void
    {
        ActivityModel::query()->whereKey($activity->id()->value())->delete();
    }
}
