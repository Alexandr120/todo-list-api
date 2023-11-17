<?php

namespace App\DTO\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rules\Enum;
use Spatie\DataTransferObject\DataTransferObject;

class TaskDTO extends DataTransferObject
{
    /**
     * Task User
     */
    public ?string $user_id;

    /**
     * Tasks title
     */
    public ?string $title;

    /**
     * Tasks description
     */
    public ?string $description;

    /**
     * Task Status
     */
    public ?int $status;

    /**
     * Tasks priority
     */
    public ?int $priority;

    /**
     * Parent task
     */
    public ?int $parent_id;

    /**
     * Validate task request data
     */
    public function validate()
    {
        $validator = \Validator::make($this->all(), $this->getRules());
        if ($validator->fails()) {
            return $validator->errors();
        }
    }

    /**
     * Tasks list filters rules
     */
    protected function getRules(): array
    {
        return [
            'user_id' => ['required'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'status' => ['required', new Enum(TaskStatus::class)],
            'priority' => ['required', new Enum(TaskPriority::class)],
            'parent_id' => ['nullable'],
            'completed_at' => ['nullable']
        ];
    }

    /**
     * Update attributes
     *
     * @param Collection $data
     * @return void
     */
    public function updateAttributes(Collection $data): void
    {
        $data->map(function ($value, $property) {
            $this->$property = $value;
        });
    }
}
