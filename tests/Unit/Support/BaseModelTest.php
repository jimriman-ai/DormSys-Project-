<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\Models\BaseModel;
use App\Support\Models\Identifiable;
use App\Support\Traits\HasJalaliDates;
use App\Support\Traits\HasUuid;
use App\Support\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use ReflectionMethod;
use Tests\Concerns\CreatesActivityLogTable;
use Tests\TestCase;

class BaseModelTest extends TestCase
{
    use CreatesActivityLogTable;

    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('pdo_sqlite')) {
            return;
        }

        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);

        Schema::create('support_base_test_models', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->softDeletes();
        });

        $this->createActivityLogTable();
    }

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
    public function it_throws_when_uuid_not_yet_assigned(): void
    {
        $model = new SupportBaseTestModel;

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Model UUID not yet assigned.');

        $model->getId();
    }

    #[Test]
    public function it_returns_uuid_after_save(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('SQLite driver is not available.');
        }

        $model = new SupportBaseTestModel;
        $model->save();

        $this->assertTrue(Uuid::isValid($model->getId()));
    }

    #[Test]
    public function it_returns_uuid_via_get_id_after_creating_event_before_save(): void
    {
        $model = new SupportBaseTestModel;

        $method = new ReflectionMethod(BaseModel::class, 'fireModelEvent');
        $method->invoke($model, 'creating');

        $this->assertFalse($model->exists);
        $this->assertTrue(Uuid::isValid($model->getId()));
    }
}

class SupportBaseTestModel extends BaseModel
{
    protected $table = 'support_base_test_models';

    public $timestamps = false;
}
