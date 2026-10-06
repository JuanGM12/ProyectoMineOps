<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests;

use App\Domain\Activity\Enums\ActivityPriority;
use App\Domain\Activity\Enums\ActivityStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListActivitiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
            'status' => ['sometimes', Rule::enum(ActivityStatus::class)],
            'priority' => ['sometimes', Rule::enum(ActivityPriority::class)],
            'responsible_id' => ['sometimes', 'uuid'],
            'scheduled_from' => ['sometimes', 'date_format:Y-m-d'],
            'scheduled_to' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:scheduled_from'],
            'sort' => ['sometimes', Rule::in(['created_at', 'scheduled_date', 'due_date', 'title', 'priority', 'status'])],
            'direction' => ['sometimes', Rule::in(['asc', 'desc'])],
        ];
    }
}
