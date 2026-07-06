<?php

declare(strict_types=1);

use Tests\Feature\Modules\Identity\StubEmployeeConsumer;

it('allows stub consumer to use only the read contract surface', function (): void {
    $created = createIdentityUserThroughMutation('Consumer User', 'consumer@example.com');

    $consumer = app(StubEmployeeConsumer::class);

    expect($consumer->canReferenceUser($created->requireId()))->toBeTrue()
        ->and($consumer->labelFor($created->requireId()))->toBe('Consumer User');
});
