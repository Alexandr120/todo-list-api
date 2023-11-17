<?php

namespace App\Http\Controllers\Api;

use App\DTO\Task\FiltersTasksDTO;
use App\DTO\Task\TaskDTO;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\TasksListFilters;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Task Management
 *
 * APIs to manage the user task resourse
 */
class TaskController extends Controller
{
    use TasksListFilters;

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Check ability user token
     *
     * @param User $user
     * @param string $ability
     * @return JsonResponse|null
     */
    private function checkAbilityToken(User $user, string $ability): ?JsonResponse
    {
        if (!$user->tokenCan($ability)) {
            return $this->sendResponse('error', 'Forbidden!', Response::HTTP_FORBIDDEN);
        }

        return null;
    }

    /**
     * Display a listing tasks.
     *
     * Get a list of tasks
     * @response 200 {
     *      "status": "success",
     *      "message": "Tasks List.",
     *      "data": {
     *          "current_page": 1,
     *          "data": [
     *               {
     *                   "id": 203,
     *                   "user_id": 11,
     *                   "title": "Task title",
     *                   "status": 1,
     *                   "priority": 1,
     *                   "description": "Some desc ......",
     *                   "parent_id": null,
     *                   "completed_at": null,
     *                   "created_at": "2023-11-16T00:29:54.000000Z"
     *               }
     *          ],
     *          "first_page_url": "http://127.0.0.1:8000/api/tasks?page=1",
     *           "from": 1,
     *           "last_page": 1,
     *           "last_page_url": "http://127.0.0.1:8000/api/tasks?page=1",
     *           "links": [
     *               {
     *                   "url": null,
     *                   "label": "&laquo; Previous",
     *                   "active": false
     *               },
     *               {
     *                   "url": "http://127.0.0.1:8000/api/tasks?page=1",
     *                   "label": "1",
     *                   "active": true
     *               },
     *               {
     *                   "url": null,
     *                   "label": "Next &raquo;",
     *                   "active": false
     *               }
     *           ],
     *           "next_page_url": null,
     *           "path": "http://127.0.0.1/api/tasks",
     *           "per_page": 20,
     *           "prev_page_url": null,
     *           "to": 2,
     *           "total": 2
     *      }
     * }
     *
     * @response 401 {
     *       "message": "Unauthenticated."
     * }
     *
     * @response 403 {
     *       "status": "error",
     *       "message": "Forbidden!"
     * }
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        if ($abilityError = $this->checkAbilityToken($user, 'read')) return $abilityError;

        $tasks = $this->getUserTasks($user)->orderBy('created_at');

        return $this->sendResponse('success', 'Tasks List.', Response::HTTP_OK, ['data' => $tasks->paginate($this->defaultPageSize)]);
    }

    /**
     * All tasks of user without tree tasks
     *
     * @param User $user
     * @return HasMany
     */
    private function getUserTasks(User $user): HasMany
    {
        return $user->tasks()->whereNull('parent_id');
    }

    /**
     * Show Task filters
     *
     * Method for Ajax filtering task list
     *
     * @response 200 {
     *   "select": {
     *       "page_size": [
     *           {
     *               "id": 10,
     *               "label": 10
     *           },
     *           {
     *               "id": 20,
     *               "label": 20
     *           },
     *           {
     *               "id": 30,
     *               "label": 30
     *           },
     *           {
     *               "id": 40,
     *               "label": 40
     *           },
     *           {
     *               "id": 50,
     *               "label": 50
     *           }
     *       ],
     *       "status": [
     *           {
     *               "id": 1,
     *               "label": "Todo"
     *           },
     *           {
     *               "id": 2,
     *               "label": "Done"
     *           }
     *       ],
     *       "priority": [
     *           {
     *               "id": 1,
     *               "label": "for future"
     *           },
     *           {
     *               "id": 2,
     *               "label": "Low"
     *           },
     *           {
     *               "id": 3,
     *               "label": "Middle"
     *           },
     *           {
     *               "id": 4,
     *               "label": "High"
     *           },
     *           {
     *               "id": 5,
     *               "label": "Urgently"
     *           }
     *       ]
     *   },
     *   "sorting": [
     *       "created_at", "priority"
     *   ],
     *   "text": [
     *       "title", "description"
     *   ]
     * }
     *
     * @return Collection
     */
    public function tasksFiltersData(): Collection
    {
        return $this->getTasksListFilters();
    }

    /**
     * Display a listing tasks with filters
     *
     * Method for Ajax filtering task list
     *
     * @bodyParam page_size int Per page. Default 20. Example: 20
     * @bodyParam sorting object Sorting by column. Default column "created_at" with direction "asc".
     * @bodyParam sorting.column string. Example: priority
     * @bodyParam sorting.direction string. Example: desc
     * @bodyParam filters object Tasks filters.
     * @bodyParam filters.status int filter - "Status". Example: 1
     * @bodyParam filters.priority int filter - "Priority". Example: 3
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxFilter(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($abilityError = $this->checkAbilityToken($user, 'read')) return $abilityError;

        $pageFilters = new FiltersTasksDTO($request->all());
        if ($errors = $pageFilters->validate()) {
            return $this->sendResponse('validate error', 'Invalid task data!', Response::HTTP_BAD_REQUEST, ['errors' => $errors]);
        }

        $tasks = $this->getUserTasks($user);

        $pageFilters->filters?->map(function ($value, $filter) use ($tasks) {
            $tasks->where($filter, $value);
        });

        $tasks->orderBy($pageFilters->sorting, $pageFilters->direction);

        return $this->sendResponse('success', 'Filter tasks', Response::HTTP_OK, ['data' => $tasks->paginate(20)]);
    }

    /**
     * Store a newly created task.
     *
     * Create new task
     *
     *
     * @bodyParam title string required Task title. Example: Task title
     * @bodyParam description string required Task description. Example: Task description
     * @bodyParam priority int required Task priority. Example: 3
     * @bodyParam parent_id int Parent task id can be null or not exist. Example: null
     *
     * @param Request  $request
     *
     * @response 200 {
     *        "status": "success",
     *        "message": "Task created successful!",
     *        "task": {
     *        "user_id": "11",
     *        "title": "Task title",
     *        "description": "Some desc ......",
     *        "status": 1,
     *        "priority": 1,
     *        "parent_id": null,
     *        "created_at": "2023-11-16T23:59:12.000000Z",
     *        "id": 206
     *     }
     * }
     *
     * @response 400 {
     *     "status": "validate error",
     *     "message": "Invalid task data!",
     *     "errors": {
     *           "title": [
     *              "The title field is required."
     *           ]
     *     }
     * }
     *
     * @response 401 {
     *       "message": "Unauthenticated."
     * }
     *
     * @response 403 {
     *       "status": "error",
     *       "message": "Forbidden!"
     * }
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if ($abilityError = $this->checkAbilityToken($user, 'write')) return $abilityError;

            $data = new TaskDTO($request->all());

            $data?->user_id ?? $data->user_id = $user->id;
            $data?->status ?? $data->status = TaskStatus::TODO();

            if ($errors = $data->validate()) {
                return $this->sendResponse('validate error', 'Invalid task data!', Response::HTTP_BAD_REQUEST, ['errors' => $errors]);
            }

            if ($data->parent_id && $errors = $this->checkParentTask($user, $data->parent_id)) {
                return $errors;
            }

            $task = Task::create($data->all());
            if (!$task->id) {
                throw new \Exception('Task not created!');
            }

            return $this->sendResponse('success', 'Task created successful!', Response::HTTP_OK, ['task' => $task]);

        } catch (\Exception $exception) {
            return $this->sendResponse('error', $exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param User $user
     * @param int $parentId
     * @return JsonResponse|null
     */
    private function checkParentTask(User $user, int $parentId): ?JsonResponse
    {
        $task = Task::findOrFail($parentId);
        if (!$task || $task->user_id !== $user->id) {
            return $this->sendResponse('error', 'Invalid parent task!', Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    /**
     * Show task with self tree subtasks.
     *
     * @param Task $task
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Task with self tree subtasks",
     *   "task": {
     *       "id": 203,
     *       "user_id": 11,
     *       "title": "Task title",
     *       "status": "1",
     *       "priority": "1",
     *       "description": "Some task description...",
     *       "parent_id": null,
     *       "completed_at": null,
     *       "created_at": "2023-11-16 00:29:54",
     *       "updated_at": "2023-11-16 00:29:54",
     *       "subtasks": [
     *           {
     *               "id": 205,
     *               "user_id": 11,
     *               "title": "Sub Task from task 203",
     *               "status": "1",
     *               "priority": "1",
     *               "description": "Some task description...",
     *               "parent_id": "203",
     *               "completed_at": null,
     *               "created_at": "2023-11-16 00:31:23",
     *               "updated_at": "2023-11-16 00:37:27",
     *               "subtasks": [
     *                   {
     *                       "id": 206,
     *                       "user_id": 11,
     *                       "title": "Sub Task from task 205",
     *                       "status": "1",
     *                       "priority": "1",
     *                       "description": "Some task description...",
     *                       "parent_id": "205",
     *                       "completed_at": null,
     *                       "created_at": "2023-11-16 23:59:12",
     *                       "updated_at": "2023-11-16 23:59:12"
     *                   }
     *               ]
     *           }
     *       ]
     *   }
     * }
     * @response 401 {
     *       "message": "Unauthenticated."
     * }
     *
     * @response 403 {
     *       "status": "error",
     *       "message": "Forbidden!"
     * }
     *
     * @return JsonResponse
     */
    public function show(Task $task): JsonResponse
    {
        $user = Auth::user();
        if ($abilityError = $this->checkAbilityToken($user, 'read')) return $abilityError;

        if ($task->user_id !== $user->id) {
            return $this->sendResponse('error', 'Forbidden!', Response::HTTP_FORBIDDEN);
        }

        $collection = $this->prepareTaskData($task);

        return $this->sendResponse('success', 'Task with self tree subtasks', Response::HTTP_OK, ['task' => $collection]);
    }

    /**
     * Prepare task collection.
     *
     * @param Task $task
     * @return Collection
     */
    private function prepareTaskData(Task $task): Collection
    {
        $collection = collect($task->getAttributes());

        if ($task->subtasks->count()) {
            $collection->put('subtasks', collect([]));
            $task->subtasks->map(function ($subtask) use ($collection) {
                $collection->get('subtasks')->push($this->prepareTaskData($subtask));
            });
        }

        return $collection;
    }

    /**
     * Update Task.
     *
     * @bodyParam title string required Task title. Example: Task title
     * @bodyParam description string required Task description. Example: Task description
     * @bodyParam priority int required Task priority. Example: 3
     *
     * @param Request  $request
     * @param Task $task
     *
     * * @response 200 {
     *     "status": "success",
     *     "message": "Task - "Task Title" updated successfully!",
     *     "task": {
     *         "user_id": "11",
     *         "title": "Task title UPDATE",
     *         "description": "Some desc ...... UPDATE",
     *         "status": 1,
     *         "priority": 3,
     *         "parent_id": null,
     *         "completed_at": null,
     *         "created_at": "2023-11-16T23:59:12.000000Z",
     *         "id": 206
     *    }
     * }
     *
     * @response 400 {
     *     "status": "validate error",
     *     "message": "Invalid task data!",
     *     "errors": {
     *           "title": [
     *              "The title field is required."
     *           ]
     *     }
     * }
     *
     * @response 401 {
     *       "message": "Unauthenticated."
     * }
     *
     * @response 403 {
     *       "status": "error",
     *       "message": "Forbidden!"
     * }
     * @return JsonResponse
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        try {
            $user = Auth::user();
            if ($abilityError = $this->checkAbilityToken($user, 'write')) return $abilityError;

            $data = new TaskDTO($task->getAttributes());

            $data->updateAttributes(collect($request->all()));

            if ($errors = $data->validate()) {
                return $this->sendResponse('success', 'Invalid task data!', Response::HTTP_BAD_REQUEST, ['errors' => $errors]);
            }

            $task->update($data->all());

            return $this->sendResponse('success', 'Task - "'.$data->title.'" updated successfully!', Response::HTTP_OK, ['task' => $task]);

        } catch (\Exception $exception) {
            return $this->sendResponse('error', $exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Complete the task
     *
     * Complete the task only if she has all completed subtask
     *
     * @param Task $task
     *
     * @response 200 {
     *       "status": "success",
     *       "message": "Task completed!",
     *       "task": {
     *         "user_id": "11",
     *         "title": "Task title",
     *         "description": "Some desc ......",
     *         "status": 2,
     *         "priority": 3,
     *         "parent_id": null,
     *         "completed_at": 2023-11-17 03:59:12,
     *         "created_at": "2023-11-16T23:59:12.000000Z",
     *         "id": 206
     *    }
     * }
     *
     * @response 400 {
     *       "status": "error",
     *       "message": "This task has enabled subtasks!"
     * }
     *
     * @response 401 {
     *       "message": "Unauthenticated."
     * }
     *
     * @response 403 {
     *       "status": "error",
     *       "message": "Forbidden!"
     * }
     *
     * @return JsonResponse
     */
    public function complete(Task $task): JsonResponse
    {
        $user = Auth::user();
        if ($abilityError = $this->checkAbilityToken($user, 'write')) return $abilityError;

        if (!$this->checkSubTasksStatus($task)) {
            $task->status = TaskStatus::DONE();
            $task->completed_at = Carbon::now()->toDateTimeString();

            $task->update();

            return $this->sendResponse('success', 'Task completed!', Response::HTTP_OK, ['task' => $task]);
        }

        return $this->sendResponse('error', 'This task has enabled subtasks!', Response::HTTP_BAD_REQUEST);
    }

    /**
     * Check for active subtasks
     *
     * @param Task $task
     * @return bool
     */
    private function checkSubTasksStatus(Task $task): bool
    {
        if ($task->subtasks->count()) {
            $activeTasks = $task->subtasks->filter(function ($subtask) {
                return $subtask->status->isInProgress();
            });

            return ($activeTasks->count());
        }

        return false;
    }

    /**
     * Remove the task.
     *
     * Delete task if status not is done
     *
     * @param Task  $task
     *
     * @response 200 {
     *       "status": "success",
     *       "message": "Task deleted successfully!"
     * }
     *
     * @response 400 {
     *       "status": "error",
     *       "message": "You cannot delete a completed task!"
     * }
     *
     * @response 401 {
     *       "message": "Unauthenticated."
     * }
     *
     * @response 403 {
     *       "status": "error",
     *       "message": "Forbidden!"
     * }
     * @return JsonResponse
     */
    public function destroy(Task $task): JsonResponse
    {
        $user = Auth::user();
        if ($abilityError = $this->checkAbilityToken($user, 'write')) return $abilityError;

        if (!$task->status->isDone()) {
            $this->deleteTask($task);

            return $this->sendResponse('success', 'Task delete successfully!', Response::HTTP_OK);
        }

        return $this->sendResponse('error', 'You cannot delete a completed task!', Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param Task $task
     * @return void
     */
    private function deleteTask(Task $task)
    {
        $subtasks = $task->subtasks;
        $task->delete();

        if ($subtasks->count()) {
            $subtasks->map(function ($subtask) {
                $this->deleteTask($subtask);
            });
        }
    }

    /**
     * @param string $status
     * @param string $message
     * @param int $code
     * @param array $otherData
     * @return JsonResponse
     */
    private function sendResponse(string $status, string $message, int $code, array $otherData=[]): JsonResponse
    {
        $responseData = array_merge([
            'status' => $status,
            'message' => $message
        ], $otherData);

        return response()->json($responseData, $code);
    }

}
