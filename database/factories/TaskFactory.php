<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => 'Task title - "'. Str::random(10).'"',
            'status' => rand(TaskStatus::TODO(), TaskStatus::DONE()),
            'priority' => rand(TaskPriority::FOR_FUTURE(), TaskPriority::URGENTLY()),
            'description' =>  'Some task description...',
            'created_at' => Carbon::now()->toDateTimeString(),
        ];
    }
}
