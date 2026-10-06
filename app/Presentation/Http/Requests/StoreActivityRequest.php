<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests;

use App\Domain\Activity\Enums\ActivityPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'area' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'responsible_id' => ['required', 'uuid'],
            'priority' => ['required', Rule::enum(ActivityPriority::class)],
            'scheduled_date' => ['required', 'date_format:Y-m-d'],
            'due_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }
}
