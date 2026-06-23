<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\Models\BaseModel;
use App\Support\Models\Identifiable;
use App\Support\Traits\HasJalaliDates;
use App\Support\Traits\HasUuid;
use App\Support\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use ReflectionMethod;
use Tests\TestCase;

class BaseModelTest extends TestCase
{
    #[Test]
    public function it_configures_uuid_primary_key_and_audit_traits(): void
    {
        $model = new SupportBaseTestModel;

        $this->assertInstanceOf(Identifiable::class, $model);
        $this->assertContains(HasUuid::class, class_uses_recursive($model));
        $this->assertContains(HasJalaliDates::class, class_uses_recursive($model));
        $this->assertContains(RecordsActivity::class, class_uses_recursive($model));
        $this->assertContains(SoftDeletes::class, class_uses_recursive($model));
        $this->assertFalse($model->getIncrementing());
        $this->assertSame('string', $model->getKeyType());
        $casts = $model->getCasts();

        $this->assertSame('datetime', $casts['created_at']);
        $this->assertSame('datetime', $casts['updated_at']);
        $this->assertSame('datetime', $casts['deleted_at']);
        $this->assertSame('string', $casts['created_by']);
        $this->assertSame('string', $casts['updated_by']);
        $this->assertSame('string', $casts['deleted_by']);
    }

    #[Test]
    public function it_assigns_uuidv7_when_creating(): void
    {
        $model = new SupportBaseTestModel;

        $method = new ReflectionMethod(BaseModel::class, 'fireModelEvent');
        $method->invoke($model, 'creating');

        $this->assertTrue(Uuid::isValid($model->getId()));
    }
}

class SupportBaseTestModel extends BaseModel
{
    protected $table = 'support_base_test_models';

    public $timestamps = false;
}
