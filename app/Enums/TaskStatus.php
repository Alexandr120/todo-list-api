<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Options;
use ArchTech\Enums\Values;

enum TaskStatus: int
{
    use InvokableCases, Options, Values;

    case TODO = 1;
    case DONE = 2;

    public function isDone(): bool
    {
        return $this === self::DONE;
    }

    public function isInProgress(): bool
    {
        return $this === self::TODO;
    }

    public static function getTaskStatusFilterData(): array
    {
        return [
            [
                'id' => self::TODO(),
                'label' => 'Todo'
            ],
            [
                'id' => self::DONE(),
                'label' => 'Done'
            ]
        ];
    }

}
