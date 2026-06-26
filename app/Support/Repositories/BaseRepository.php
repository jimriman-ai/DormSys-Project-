<?php

declare(strict_types=1);

namespace App\Support\Repositories;

use App\Support\Contracts\Repositories\BaseRepositoryInterface;
use App\Support\Exceptions\NotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Generic Eloquent repository with CRUD, query helpers, and soft-delete support.
 *
 * @template TModel of Model
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * @param  TModel  $model
     */
    public function __construct(protected Model $model) {}

    public function find(string $id): ?Model
    {
        return $this->query()->find($id);
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->query()->get($columns);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Model
    {
        return $this->query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(string $id, array $attributes): bool
    {
        $model = $this->findOrFail($id);

        return $model->update($attributes);
    }

    public function delete(string $id): bool
    {
        $model = $this->findOrFail($id);

        return (bool) $model->delete();
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, Model> $paginator */
        $paginator = $this->query()->paginate($perPage, $columns);

        return $paginator;
    }

    public function findOrFail(string $id): Model
    {
        $model = $this->find($id);

        if ($model === null) {
            throw new NotFoundException(sprintf(
                'Record not found in [%s] with id [%s].',
                $this->model->getTable(),
                $id
            ));
        }

        return $model;
    }

    /**
     * @param  array<string, mixed>  $criteria
     * @param  array<string>  $columns
     * @return Collection<int, Model>
     */
    public function findBy(array $criteria, array $columns = ['*']): Collection
    {
        return $this->applyCriteria($this->query(), $criteria)->get($columns);
    }

    /**
     * @param  array<string, mixed>  $criteria
     * @param  array<string>  $columns
     */
    public function firstWhere(array $criteria, array $columns = ['*']): ?Model
    {
        return $this->applyCriteria($this->query(), $criteria)->first($columns);
    }

    public function exists(string $id): bool
    {
        return $this->query()->whereKey($id)->exists();
    }

    public function count(): int
    {
        return $this->query()->count();
    }

    /**
     * @return Builder<TModel>
     */
    public function query(): Builder
    {
        /** @var Builder<TModel> */
        return $this->model->newQuery();
    }

    public function restore(string $id): bool
    {
        $this->assertSoftDeletes();

        $query = $this->query();
        /** @phpstan-ignore method.notFound */
        $model = $query->withTrashed()->find($id);

        if ($model === null) {
            throw new NotFoundException(sprintf(
                'Record not found in [%s] with id [%s].',
                $this->model->getTable(),
                $id
            ));
        }

        return (bool) $model->restore();
    }

    public function forceDelete(string $id): bool
    {
        $this->assertSoftDeletes();

        $query = $this->query();
        /** @phpstan-ignore method.notFound */
        $model = $query->withTrashed()->find($id);

        if ($model === null) {
            throw new NotFoundException(sprintf(
                'Record not found in [%s] with id [%s].',
                $this->model->getTable(),
                $id
            ));
        }

        return (bool) $model->forceDelete();
    }

    /**
     * @param  array<string>  $columns
     * @return Collection<int, Model>
     */
    public function onlyTrashed(array $columns = ['*']): Collection
    {
        $this->assertSoftDeletes();

        $query = $this->query();

        /** @phpstan-ignore method.notFound */
        return $query->onlyTrashed()->get($columns);
    }

    /**
     * @param  array<string, mixed>  $criteria
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    protected function applyCriteria(Builder $query, array $criteria): Builder
    {
        foreach ($criteria as $column => $value) {
            $query->where($column, $value);
        }

        return $query;
    }

    protected function assertSoftDeletes(): void
    {
        if (! $this->usesSoftDeletes()) {
            throw new \LogicException(sprintf(
                'Model [%s] does not use soft deletes.',
                $this->model::class
            ));
        }
    }

    protected function usesSoftDeletes(): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($this->model), true);
    }
}
