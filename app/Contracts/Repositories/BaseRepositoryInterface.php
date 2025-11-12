<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    /**
     * Get all records
     */
    public function all(): Collection;

    /**
     * Find a record by ID
     */
    public function find(int $id): ?Model;

    /**
     * Create a new record
     */
    public function create(array $data): Model;

    /**
     * Update a record
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a record
     */
    public function delete(int $id): bool;

    /**
     * Find by specific column
     */
    public function findBy(string $column, mixed $value): ?Model;
}
