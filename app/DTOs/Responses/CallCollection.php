<?php

namespace App\DTOs\Responses;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Call Collection DTO
 *
 * Response DTO for multiple calls representation.
 * Provides consistent API response structure for collections.
 */
readonly class CallCollection
{
    /**
     * @param Collection<int, CallResource> $items
     */
    public function __construct(
        public Collection $items,
        public ?int $total = null,
        public ?int $per_page = null,
        public ?int $current_page = null,
        public ?int $last_page = null,
        public ?string $next_page_url = null,
        public ?string $prev_page_url = null,
    ) {}

    /**
     * Create collection from array of models
     *
     * @param iterable $calls
     * @return self
     */
    public static function fromModels(iterable $calls): self
    {
        $items = collect($calls)->map(fn($call) => CallResource::fromModel($call));
        
        return new self(
            items: $items,
        );
    }

    /**
     * Create collection from paginator
     *
     * @param LengthAwarePaginator $paginator
     * @return self
     */
    public static function fromPaginator(LengthAwarePaginator $paginator): self
    {
        $items = collect($paginator->items())->map(fn($call) => CallResource::fromModel($call));
        
        return new self(
            items: $items,
            total: $paginator->total(),
            per_page: $paginator->perPage(),
            current_page: $paginator->currentPage(),
            last_page: $paginator->lastPage(),
            next_page_url: $paginator->nextPageUrl(),
            prev_page_url: $paginator->previousPageUrl(),
        );
    }

    /**
     * Convert to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $result = [
            'data' => $this->items->map(fn(CallResource $resource) => $resource->toArray())->values()->toArray(),
        ];

        if ($this->total !== null) {
            $result['meta'] = [
                'total' => $this->total,
                'per_page' => $this->per_page,
                'current_page' => $this->current_page,
                'last_page' => $this->last_page,
            ];

            $result['links'] = [
                'next' => $this->next_page_url,
                'prev' => $this->prev_page_url,
            ];
        }

        return $result;
    }

    /**
     * Convert to JSON
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Get count of items
     */
    public function count(): int
    {
        return $this->items->count();
    }

    /**
     * Check if collection is empty
     */
    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    /**
     * Get total duration of all calls in seconds
     */
    public function getTotalDuration(): int
    {
        return $this->items->sum(fn(CallResource $call) => $call->duration_seconds ?? 0);
    }

    /**
     * Get average duration of all calls in seconds
     */
    public function getAverageDuration(): float
    {
        $completed = $this->items->filter(fn(CallResource $call) => $call->duration_seconds !== null);
        
        if ($completed->isEmpty()) {
            return 0;
        }

        return $completed->avg(fn(CallResource $call) => $call->duration_seconds);
    }
}
