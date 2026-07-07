<?php

declare(strict_types=1);

use PrintAgent\Sdk\Builders\KitchenTicketBuilder;

it('builds a kitchen ticket with modifiers and notes', function () {
    $document = KitchenTicketBuilder::make()
        ->orderNumber('1042')
        ->table('7')
        ->item('Cheeseburger', 2, ['No onions', 'Extra cheese'])
        ->item('Fries', 1)
        ->note('Rush — customer waiting')
        ->cut()
        ->toArray();

    $texts = collect($document['sections'][0]['elements'])
        ->filter(fn (array $element) => $element['type'] === 'text')
        ->pluck('content')
        ->all();

    expect($texts)->toContain('ORDER #1042', 'Table 7', '2x Cheeseburger', '  - No onions', '  - Extra cheese', '1x Fries');
});

it('includes a timestamp line when requested', function () {
    $time = \Carbon\CarbonImmutable::create(2026, 1, 1, 12, 0, 0);
    $document = KitchenTicketBuilder::make()->time($time)->toArray();

    expect($document['sections'][0]['elements'][0]['content'])->toBe('2026-01-01 12:00:00');
});
