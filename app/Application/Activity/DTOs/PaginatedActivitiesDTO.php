<?php

declare(strict_types=1);

namespace App\Application\Activity\DTOs;

final readonly class PaginatedActivitiesDTO
{
    /** @param list<ActivityDTO> $items */
    public function __construct(
        public array $items,
        public int $total,
        public int $currentPage,
        public int $perPage,
        public int $lastPage,
    ) {}
}
