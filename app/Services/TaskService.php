<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class TaskService implements TaskServiceInterface
{
    /**
     * @param int $id
     * @return Task|null
     */
    public function findById(int $id): ?Task
    {
        return Task::find($id);
    }

    /**
     * @param Collection $data
     * @return Task
     * @throws \Exception
     */
    public function create(Collection $data): Task
    {
        if ($parentTaskId = $data->get('parent_id')) {
            $parentTask = $this->findById($parentTaskId);

            if (!$parentTask || $parentTask->user_id !== Auth::user()->id)
                throw new \Exception('Invalid parent task!');
        }

        $task = Task::create($data->all());
        if (!$task->id) {
            throw new \Exception('Task not created!');
        }

        return $task;
    }

    /**
     * @param Collection $data
     * @return Task
     */
    public function update(Collection $data, Task $task): Task
    {
        $task->update($data->all());

        return $task;
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }

}
