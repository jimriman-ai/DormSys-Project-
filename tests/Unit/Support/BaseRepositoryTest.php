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
use Tests\Concerns\CreatesActivityLogTable;
use Tests\TestCase;

class BaseRepositoryTest extends TestCase
{
    use CreatesActivityLogTable;

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

        $this->createActivityLogTable();
    }

    #[Test]
    public function it_performs_crud_operations(): void
    {
        $repository = new SupportTestRepository(new SupportRepositoryTestModel);

        /** @var SupportRepositoryTestModel $created */
        $created = $repository->create(['name' => 'alpha']);
        $this->assertSame('alpha', $created->name);
        $this->assertTrue(Uuid::isValid((string) $created->getKey()));

        $found = $repository->find((string) $created->getKey());
        $this->assertNotNull($found);
        /** @var SupportRepositoryTestModel $found */
        $this->assertTrue($repository->exists((string) $created->getKey()));
        $this->assertSame(1, $repository->count());

        $this->assertTrue($repository->update((string) $created->getKey(), ['name' => 'beta']));
        /** @var SupportRepositoryTestModel $updated */
        $updated = $repository->findOrFail((string) $created->getKey());
        $this->assertSame('beta', $updated->name);

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
        /** @var SupportRepositoryTestModel $firstMatch */
        $firstMatch = $matches->first();
        $this->assertNotNull($firstMatch);
        $this->assertSame('second', $firstMatch->name);
        /** @var SupportRepositoryTestModel|null $firstWhere */
        $firstWhere = $repository->firstWhere(['name' => 'second']);
        $this->assertNotNull($firstWhere);
        $this->assertSame('second', $firstWhere->name);
    }

    #[Test]
    public function it_throws_when_record_is_missing(): void
    {
        $repository = new SupportTestRepository(new SupportRepositoryTestModel);

        $this->expectException(NotFoundException::class);

        $repository->findOrFail(Uuid::uuid7()->toString());
    }
}

/**
 * @property string $name
 */
class SupportRepositoryTestModel extends BaseModel
{
    protected $table = 'support_repository_test_models';

    protected $guarded = [];
}

/**
 * @extends BaseRepository<SupportRepositoryTestModel>
 */
class SupportTestRepository extends BaseRepository {}
