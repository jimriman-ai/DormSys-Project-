<?php

declare(strict_types=1);

use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Identity\Application\Services\DeactivateUserAction;
use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Spatie\Activitylog\Models\Activity;

it('records activity log entries on user create and disable', function (): void {
    $created = app(CreateUserAction::class)->execute('Audit User', 'audit@example.com');

    expect(Activity::query()
        ->where('subject_type', UserModel::class)
        ->where('subject_id', $created->id->value)
        ->where('event', 'created')
        ->exists())->toBeTrue();

    app(DeactivateUserAction::class)->execute($created->id);

    expect(Activity::query()
        ->where('subject_type', UserModel::class)
        ->where('subject_id', $created->id->value)
        ->where('event', 'updated')
        ->exists())->toBeTrue();
});
