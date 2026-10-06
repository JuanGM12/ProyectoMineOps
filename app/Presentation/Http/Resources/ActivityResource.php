<?php

declare(strict_types=1);

namespace App\Presentation\Http\Resources;

use App\Application\Activity\DTOs\ActivityDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ActivityResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var ActivityDTO $activity */
        $activity = $this->resource;

        return [
            'id' => $activity->id,
            'code' => $activity->code,
            'title' => $activity->title,
            'description' => $activity->description,
            'area' => $activity->area,
            'location' => $activity->location,
            'responsible_id' => $activity->responsibleId,
            'priority' => $activity->priority,
            'status' => $activity->status,
            'scheduled_date' => $activity->scheduledDate,
            'due_date' => $activity->dueDate,
        ];
    }
}
