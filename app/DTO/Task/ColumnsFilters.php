<?php

namespace App\DTO\Task;

use Illuminate\Support\Collection;
use Spatie\DataTransferObject\DataTransferObject;

class ColumnsFilters extends DataTransferObject
{
    /**
     * Tasks filter title
     */
    public ?string $title;

    /**
     * Tasks filter description
     */
    public ?string $description;

    /**
     * Tasks filter status
     */
    public ?string $status;

    /**
     * Tasks filter priority
     */
    public ?string $priority;

    /**
     * Return filters if not empty
     *
     * @return Collection
     */
    public function getActiveFilters(): Collection
    {
        return collect(array_filter($this->all(), function ($value) {
                return ($value);
            })
        );
    }

}
