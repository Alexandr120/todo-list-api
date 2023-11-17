<?php

namespace App\DTO\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rules\Enum;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;
use App\DTO\Task\Casters\ColumnsFiltersCaster;
use Illuminate\Validation\Rule;

class FiltersTasksDTO extends DataTransferObject
{
    /**
     * Page size
     * Default 20
     */
    public int $page_size = 20;

    /**
     * Tasks sorting column
     * Default column "created_at"
     */
    #[MapFrom('sorting.column')]
    public string $sorting = 'created_at';

    /**
     * Tasks sorting direction
     * Default direction "asc"
     */
    #[MapFrom('sorting.direction')]
    public string $direction = 'asc';

    /**
     * Column filters from request
     */
    #[CastWith(ColumnsFiltersCaster::class)]
    public ?Collection $filters;

    /**
     * Validate filters data
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
            'page_size' => ['nullable', 'integer'],
            'sorting' => ['nullable', Rule::in('created_at', 'priority')],
            'direction' => ['nullable', Rule::in('asc', 'desc')],
            'filters.title' => ['nullable', 'string', 'max:255'],
            'filters.description' => ['nullable', 'string'],
            'filters.status' => ['nullable', new Enum(TaskStatus::class)],
            'filters.priority' => ['nullable', new Enum(TaskPriority::class)],
        ];
    }
}
