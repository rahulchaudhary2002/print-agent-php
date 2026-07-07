<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO;

/**
 * @template T
 */
final readonly class PaginatedResult
{
    /**
     * @param  array<int, T>  $items
     */
    public function __construct(
        public array $items,
        public int $page,
        public int $pageSize,
        public int $total,
        public int $totalPages,
    ) {}

    /**
     * @template TItem
     *
     * @param  array<string, mixed>  $data
     * @param  callable(array<string, mixed>): TItem  $mapItem
     * @return self<TItem>
     */
    public static function fromArray(array $data, callable $mapItem): self
    {
        /** @var array<int, array<string, mixed>> $rawItems */
        $rawItems = $data['items'] ?? [];
        $pagination = $data['pagination'] ?? [];

        return new self(
            items: array_map($mapItem, $rawItems),
            page: (int) ($pagination['page'] ?? 1),
            pageSize: (int) ($pagination['pageSize'] ?? count($rawItems)),
            total: (int) ($pagination['total'] ?? count($rawItems)),
            totalPages: (int) ($pagination['totalPages'] ?? 1),
        );
    }
}
