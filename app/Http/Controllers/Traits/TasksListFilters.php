<?php

namespace App\Http\Controllers\Traits;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Support\Collection;

trait TasksListFilters
{
    public int $defaultPageSize = 20;

    public function getTasksListFilters(): Collection
    {
        return new Collection([
            'select' => [
                'page_size' => $this->preparePagSeizes(),
                'status' => TaskStatus::getTaskStatusFilterData(),
                'priority' => TaskPriority::getTaskPrioritiesFilterData()
            ],
            'sorting' => [
                'created_at',
                'priority'
            ],
            'text' => [
                'title',
                'description'
            ]
        ]);
    }

    private function preparePagSeizes(): array
    {
        return array_map(function ($v) {
            $size = $v*10;
            return ['id' => $size, 'label' => $size];
        }, range(1, 5));
    }
}
