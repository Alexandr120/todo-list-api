<?php

namespace App\DTO\Task\Casters;

use Illuminate\Support\Collection;
use Spatie\DataTransferObject\Caster;
use App\DTO\Task\ColumnsFilters;

class ColumnsFiltersCaster implements Caster
{
    public function cast(mixed $value): Collection
    {
        $filters = new ColumnsFilters($value);

        return collect(array_filter($filters->all(), function ($v){
            return ($v);
        }));
    }
}
