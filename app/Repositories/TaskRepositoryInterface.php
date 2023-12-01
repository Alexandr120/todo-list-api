<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

interface TaskRepositoryInterface
{
    /**
     * @param Collection|null $filters
     * @return HasMany
     */
    public function getUserTaskListWithFilters(?Collection $filters): HasMany;

    /**
     * @param Task $task
     * @return Collection
     */
    public function getTask(Task $task): Collection;
}
