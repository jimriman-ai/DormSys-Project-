<?php

declare(strict_types=1);

use App\Modules\Identity\Application\Services\CreateUserAction;
use Tests\Feature\Modules\Identity\StubEmployeeConsumer;

it('allows stub consumer to use only the read contract surface', function (): void {
    $created = app(CreateUserAction::class)->execute('Consumer User', 'consumer@example.com');

    $consumer = app(StubEmployeeConsumer::class);

    expect($consumer->canReferenceUser($created->requireId()))->toBeTrue()
        ->and($consumer->labelFor($created->requireId()))->toBe('Consumer User');
});
