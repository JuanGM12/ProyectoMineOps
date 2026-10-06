<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Api\V1;

use App\Application\Activity\Bus\CommandBus;
use App\Application\Activity\Bus\QueryBus;
use App\Application\Activity\Commands\CancelActivity\CancelActivityCommand;
use App\Application\Activity\Commands\CompleteActivity\CompleteActivityCommand;
use App\Application\Activity\Commands\CreateActivity\CreateActivityCommand;
use App\Application\Activity\Commands\StartActivity\StartActivityCommand;
use App\Application\Activity\Commands\UpdateActivity\UpdateActivityCommand;
use App\Application\Activity\DTOs\ActivityDTO;
use App\Application\Activity\DTOs\PaginatedActivitiesDTO;
use App\Application\Activity\Queries\GetActivities\GetActivitiesQuery;
use App\Application\Activity\Queries\GetActivityById\GetActivityByIdQuery;
use App\Domain\Activity\Enums\ActivityPriority;
use App\Domain\Activity\Enums\ActivityStatus;
use App\Presentation\Http\Requests\ListActivitiesRequest;
use App\Presentation\Http\Requests\StoreActivityRequest;
use App\Presentation\Http\Requests\UpdateActivityRequest;
use App\Presentation\Http\Resources\ActivityResource;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class ActivityController
{
    public function __construct(
        private CommandBus $commandBus,
        private QueryBus $queryBus,
    ) {}

    public function store(StoreActivityRequest $request): JsonResponse
    {
        $data = $request->validated();
        $activity = $this->commandBus->dispatch(new CreateActivityCommand(
            code: $data['code'],
            title: $data['title'],
            description: $data['description'] ?? null,
            area: $data['area'],
            location: $data['location'],
            responsibleId: $data['responsible_id'],
            priority: ActivityPriority::from($data['priority']),
            scheduledDate: new DateTimeImmutable($data['scheduled_date']),
            dueDate: isset($data['due_date']) ? new DateTimeImmutable($data['due_date']) : null,
        ));

        return $this->activityResponse($request, $activity, 201);
    }

    public function index(ListActivitiesRequest $request): JsonResponse
    {
        return $this->paginatedResponse($request, $this->queryBus->ask($this->listQuery($request)));
    }

    public function show(Request $request, string $id): JsonResponse
    {
        return $this->activityResponse(
            $request,
            $this->queryBus->ask(new GetActivityByIdQuery($id)),
        );
    }

    public function update(UpdateActivityRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();
        $activity = $this->commandBus->dispatch(new UpdateActivityCommand(
            activityId: $id,
            title: $data['title'],
            description: $data['description'] ?? null,
            area: $data['area'],
            location: $data['location'],
            responsibleId: $data['responsible_id'],
            priority: ActivityPriority::from($data['priority']),
            scheduledDate: new DateTimeImmutable($data['scheduled_date']),
            dueDate: isset($data['due_date']) ? new DateTimeImmutable($data['due_date']) : null,
        ));

        return $this->activityResponse($request, $activity);
    }

    public function start(Request $request, string $id): JsonResponse
    {
        return $this->activityResponse($request, $this->commandBus->dispatch(new StartActivityCommand($id)));
    }

    public function complete(Request $request, string $id): JsonResponse
    {
        return $this->activityResponse($request, $this->commandBus->dispatch(new CompleteActivityCommand($id)));
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        return $this->activityResponse($request, $this->commandBus->dispatch(new CancelActivityCommand($id)));
    }

    public function pending(ListActivitiesRequest $request): JsonResponse
    {
        return $this->paginatedResponse(
            $request,
            $this->queryBus->ask($this->listQuery($request, status: ActivityStatus::PENDING)),
        );
    }

    public function overdue(ListActivitiesRequest $request): JsonResponse
    {
        return $this->paginatedResponse(
            $request,
            $this->queryBus->ask($this->listQuery($request, overdue: true)),
        );
    }

    public function responsible(ListActivitiesRequest $request, string $responsibleId): JsonResponse
    {
        return $this->paginatedResponse(
            $request,
            $this->queryBus->ask($this->listQuery($request, responsibleId: $responsibleId)),
        );
    }

    private function listQuery(
        ListActivitiesRequest $request,
        ?ActivityStatus $status = null,
        ?string $responsibleId = null,
        bool $overdue = false,
    ): GetActivitiesQuery {
        $data = $request->validated();

        return new GetActivitiesQuery(
            page: (int) ($data['page'] ?? 1),
            perPage: (int) ($data['per_page'] ?? 15),
            status: $status ?? (isset($data['status']) ? ActivityStatus::from($data['status']) : null),
            priority: isset($data['priority']) ? ActivityPriority::from($data['priority']) : null,
            responsibleId: $responsibleId ?? ($data['responsible_id'] ?? null),
            scheduledFrom: isset($data['scheduled_from']) ? new DateTimeImmutable($data['scheduled_from']) : null,
            scheduledTo: isset($data['scheduled_to']) ? new DateTimeImmutable($data['scheduled_to']) : null,
            overdue: $overdue,
            sort: $data['sort'] ?? 'created_at',
            direction: $data['direction'] ?? 'desc',
        );
    }

    private function activityResponse(Request $request, ActivityDTO $activity, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => (new ActivityResource($activity))->toArray($request),
        ], $status);
    }

    private function paginatedResponse(Request $request, PaginatedActivitiesDTO $page): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => array_map(
                static fn (ActivityDTO $activity): array => (new ActivityResource($activity))->toArray($request),
                $page->items,
            ),
            'meta' => [
                'current_page' => $page->currentPage,
                'per_page' => $page->perPage,
                'total' => $page->total,
                'last_page' => $page->lastPage,
            ],
        ]);
    }
}
