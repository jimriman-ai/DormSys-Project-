<?php

declare(strict_types=1);

namespace App\Support\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Base Repository Interface
 *
 * Defines the contract for all repository implementations in the application.
 * Repositories are responsible for data persistence and retrieval logic.
 */
interface BaseRepositoryInterface
{
    /**
     * Find a model by its primary key.
     *
     * @param  string  $id  The UUID of the model
     * @return Model|null The found model or null if not found
     */
    public function find(string $id): ?Model;

    /**
     * Retrieve all models.
     *
     * @param  array<string>  $columns  The columns to retrieve
     * @return Collection<int, Model> Collection of all models
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Create a new model.
     *
     * @param  array<string, mixed>  $attributes  The model attributes
     * @return Model The created model
     */
    public function create(array $attributes): Model;

    /**
     * Update an existing model.
     *
     * @param  string  $id  The UUID of the model
     * @param  array<string, mixed>  $attributes  The attributes to update
     * @return bool True if update was successful
     */
    public function update(string $id, array $attributes): bool;

    /**
     * Delete a model by its primary key.
     *
     * @param  string  $id  The UUID of the model
     * @return bool True if deletion was successful
     */
    public function delete(string $id): bool;

    /**
     * Paginate the models.
     *
     * @param  int  $perPage  Number of items per page
     * @param  array<string>  $columns  The columns to retrieve
     * @return LengthAwarePaginator<int, Model> Paginated results
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;
}
