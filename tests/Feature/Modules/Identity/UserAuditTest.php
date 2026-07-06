<?php

declare(strict_types=1);

use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;
use Spatie\Activitylog\Models\Activity;

it('records activity log entries on user create and disable', function (): void {
    $created = createIdentityUserThroughMutation('Audit User', 'audit@example.com');

    expect(Activity::query()
        ->where('subject_type', UserModel::class)
        ->where('subject_id', $created->requireId()->value)
        ->where('event', 'created')
        ->exists())->toBeTrue();

    deactivateUserThroughMutation($created->requireId());

    expect(Activity::query()
        ->where('subject_type', UserModel::class)
        ->where('subject_id', $created->requireId()->value)
        ->where('event', 'updated')
        ->exists())->toBeTrue();
});
