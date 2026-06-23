<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\Exceptions\NotFoundException;
use App\Support\Models\BaseModel;
use App\Support\Repositories\BaseRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class BaseRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('SQLite driver is not available.');
        }

        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);

        Schema::create('support_repository_test_models', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
        });
    }

    #[Test]
    public function it_performs_crud_operations(): void
    {
        $repository = new SupportTestRepository(new SupportRepositoryTestModel);

        $created = $repository->create(['name' => 'alpha']);
        $this->assertSame('alpha', $created->name);
        $this->assertTrue(Uuid::isValid((string) $created->getKey()));

        $found = $repository->find((string) $created->getKey());
        $this->assertNotNull($found);
        $this->assertTrue($repository->exists((string) $created->getKey()));
        $this->assertSame(1, $repository->count());

        $this->assertTrue($repository->update((string) $created->getKey(), ['name' => 'beta']));
        $this->assertSame('beta', $repository->findOrFail((string) $created->getKey())->name);

        $this->assertTrue($repository->delete((string) $created->getKey()));
        $this->assertNull($repository->find((string) $created->getKey()));
        $this->assertSame(1, $repository->onlyTrashed()->count());

        $this->assertTrue($repository->restore((string) $created->getKey()));
        $this->assertNotNull($repository->find((string) $created->getKey()));
    }

    #[Test]
    public function it_finds_records_by_criteria(): void
    {
        $repository = new SupportTestRepository(new SupportRepositoryTestModel);
        $repository->create(['name' => 'first']);
        $repository->create(['name' => 'second']);

        $matches = $repository->findBy(['name' => 'second']);

        $this->assertCount(1, $matches);
        $this->assertSame('second', $matches->first()?->name);
        $this->assertSame('second', $repository->firstWhere(['name' => 'second'])?->name);
    }

    #[Test]
    public function it_throws_when_record_is_missing(): void
    {
        $repository = new SupportTestRepository(new SupportRepositoryTestModel);

        $this->expectException(NotFoundException::class);

        $repository->findOrFail(Uuid::uuid7()->toString());
    }
}

class SupportRepositoryTestModel extends BaseModel
{
    protected $table = 'support_repository_test_models';

    protected $guarded = [];
}

class SupportTestRepository extends BaseRepository {}
