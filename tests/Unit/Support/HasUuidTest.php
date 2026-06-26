<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use ReflectionMethod;
use Tests\TestCase;

class HasUuidTest extends TestCase
{
    #[Test]
    public function it_assigns_uuidv7_primary_key_on_model_creation(): void
    {
        $model = new UuidTestModel;

        $method = new ReflectionMethod(Model::class, 'fireModelEvent');
        $method->invoke($model, 'creating');

        $id = $model->getKey();

        $this->assertIsString($id);
        $this->assertTrue(Uuid::isValid((string) $id));
        $this->assertSame(7, Uuid::fromString((string) $id)->getVersion());
        $this->assertFalse($model->getIncrementing());
        $this->assertSame('string', $model->getKeyType());
    }
}

class UuidTestModel extends Model
{
    use HasUuid;

    protected $table = 'support_has_uuid_test';

    public $timestamps = false;

    protected $guarded = [];
}
