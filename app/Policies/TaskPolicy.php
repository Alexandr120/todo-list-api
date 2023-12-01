<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if can be view tasks list.
     *
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view tasks list', 'api');
    }

    /**
     * Determine if can be view task.
     *
     * @param  User  $user
     * @return bool
     */
    public function view(User $user, Task $task): bool
    {
        return $user->hasPermissionTo('view task', 'api') && $user->id === $task->user_id;
    }

    /**
     * Determine if the given task can be created by the user.
     *
     * @param  User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('edit task', 'api');
    }


    /**
     * Determine if the given task can be updated by the user.
     *
     * @param  User  $user
     * @param  Task  $task
     * @return bool
     */
    public function update(User $user, Task $task): bool
    {
        return $user->hasPermissionTo('edit task', 'api') && $user->id === $task->user_id;
    }

    /**
     * Determine if the given task can be deleted by the user.
     *
     * @param  User  $user
     * @return bool
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->hasPermissionTo('delete task', 'api') && $user->id === $task->user_id;
    }
}
