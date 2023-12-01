<?php

namespace App\Repositories;

use App\DTO\Task\FiltersTasksDTO;
use App\Exceptions\TasksListFiltersException;
use App\Models\Task;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

use function collect;

class TaskRepository implements TaskRepositoryInterface
{
    /**
     * @return HasMany
     */
    public function getUserTaskListWithFilters(?Collection $filters): HasMany
    {
        $tasks = Auth::user()->tasks()->whereNull('parent_id');

        $filters?->map(function ($value, $filter) use ($tasks) {
            $tasks->where($filter, $value);
        });

        return $tasks;
    }

    public function getTask(Task $task): Collection
    {
        $collection = collect($task->getAttributes());

        if ($task->subtasks->count()) {
            $collection->put('subtasks', collect([]));
            $task->subtasks->map(function ($subtask) use ($collection) {
                $collection->get('subtasks')->push($this->getTask($subtask));
            });
        }

        return $collection;
    }
}
