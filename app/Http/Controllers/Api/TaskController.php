<?php

namespace App\Http\Controllers\Api;

use App\DTO\Task\FiltersTasksDTO;
use App\DTO\Task\TaskDTO;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Repositories\TaskRepository;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Task Management
 *
 * APIs to manage the user task resourse
 */
class TaskController extends Controller
{
    /**
     * The task repository instance.
     *
     * @var TaskRepository
     */
    protected TaskRepository $taskRepository;

    /**
     * The task service instance.
     *
     * @var TaskService
     */
    protected TaskService $taskService;

    /**
     * @param TaskRepository $taskRepository
     * @param TaskService $taskService
     */
    public function __construct(TaskRepository $taskRepository, TaskService $taskService)
    {
        $this->middleware('auth:sanctum');

        $this->taskRepository = $taskRepository;
        $this->taskService = $taskService;
    }

    /**
     * Check policy
     *
     * @param mixed $task
     * @param string $method
     * @return JsonResponse|null
     */
    private function checkPolicy(string $method, mixed $task=null): ?JsonResponse
    {
        $task = $task ?? Task::class;

        $response = Gate::inspect($method, $task);

        if (!$response->allowed()) return $this->sendResponse('error', 'Forbidden!', Response::HTTP_FORBIDDEN);

        return null;
    }

    /**
     * Display a listing tasks.
     *
     * Get a list of tasks
     *
     * @param Request  $request
     * @bodyParam page_size int Per page. Default 20. Example: 20
     * @bodyParam sorting object Sorting by column. Default column "created_at" with direction "asc".
     * @bodyParam sorting.column string. Example: priority
     * @bodyParam sorting.direction string. Example: desc
     * @bodyParam filters object Tasks filters.
     * @bodyParam filters.status int filter - "Status". Example: 1
     * @bodyParam filters.priority int filter - "Priority". Example: 3
     *
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
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        if ($policyError = $this->checkPolicy('viewAny')) return $policyError;

        try {
            $listFilters = new FiltersTasksDTO($request->all());

        } catch (UnknownProperties $e) {
            return $this->sendResponse('error', $e->getMessage(), $e->getCode());
        }

        if ($errors = $listFilters->validate()) return $this->sendValidateErrors('Invalid filters data!', $errors->getMessages());

        $tasks = $this->taskRepository->getUserTaskListWithFilters($listFilters->filters)
            ->orderBy($listFilters->sorting, $listFilters->direction)
            ->paginate($listFilters->page_size);

        return $this->sendResponse('success', 'Tasks list by user.', Response::HTTP_OK, ['list' => $tasks]);
    }

    /**
     * Store a newly created task.
     *
     * Create new task
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
            if ($policyError = $this->checkPolicy('create')) return $policyError;

            $data = new TaskDTO($request->all());
            $data->user_id = Auth::user()->id;
            $data->status = TaskStatus::TODO();

            if ($errors = $data->validate()) return $this->sendValidateErrors('Invalid task data!', $errors->getMessages());

            $task = $this->taskService->create($data->getCollection());

            return $this->sendResponse('success', 'Task created successful!', Response::HTTP_OK, ['task' => $task]);

        } catch (\Exception $exception) {
            return $this->sendResponse('error', $exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
        if ($policyError = $this->checkPolicy('view', $task)) return $policyError;

        $collection = $this->taskRepository->getTask($task);

        return $this->sendResponse('success', 'Task with self tree subtasks', Response::HTTP_OK, ['task' => $collection]);
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
            if ($policyError = $this->checkPolicy('update', $task)) return $policyError;

            $data = new TaskDTO($task->getAttributes());

            $data->updateAttributes(collect($request->all()));

            if ($errors = $data->validate()) return $this->sendValidateErrors('Invalid task data!', $errors->getMessages());

            $responseMessage = 'Task - "'.$data->title.'" updated successfully!';

            $activeSubtask = collect([]);

            if ($data->status == TaskStatus::DONE()) {

                if ($task->subtasks->count()) {
                    $activeSubtask = $task->subtasks->filter(function ($subtask) {
                        return $subtask->status->isInProgress();
                    });
                }

                if ($activeSubtask->count()) {
                    $data->status = $task->status->getStatus();
                    return $this->sendResponse('error', 'This task has enabled subtasks!', Response::HTTP_OK);
                }

                $data->completed_at = Carbon::now()->toDateTimeString();
                $responseMessage = 'Task - "'.$data->title.'" completed successfully!';
            }

            return $this->sendResponse('success', $responseMessage, Response::HTTP_OK, [
                'task' => $this->taskService->update($data->getCollection(), $task)
            ]);

        } catch (\Exception $exception) {
            return $this->sendResponse('error', $exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
        if ($policyError = $this->checkPolicy('delete', $task)) return $policyError;

        if (!$task->status->isDone()) {
            $this->taskService->delete($task);

            return $this->sendResponse('success', 'Task delete successfully!', Response::HTTP_OK);
        }

        return $this->sendResponse('error', 'You cannot delete a completed task!', Response::HTTP_OK);
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

    /**
     * @param string $message
     * @param array $errors
     * @return JsonResponse
     */
    private function sendValidateErrors(string $message, array $errors): JsonResponse
    {
        return $this->sendResponse('validate error', $message, Response::HTTP_BAD_REQUEST, ['errors' => $errors]);
    }
}
