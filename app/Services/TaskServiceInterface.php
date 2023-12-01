<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Collection;

interface TaskServiceInterface
{
    /**
     * @param int $id
     * @return Task|null
     */
    public function findById(int $id): ?Task;

    /**
     * @param Collection $data
     * @return Task
     */
    public function create(Collection $data): Task;

    /**
     * @param Collection $data
     * @param Task $task
     * @return Task
     */
    public function update(Collection $data, Task $task): Task;

    /**
     * @param Task $task
     * @return mixed
     */
    public function delete(Task $task);


}
