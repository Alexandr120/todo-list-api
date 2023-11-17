<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Options;
use ArchTech\Enums\Values;

enum TaskPriority: int
{
    use InvokableCases, Options, Values;

    case FOR_FUTURE = 1;
    case LOW = 2;
    case MIDDLE = 3;
    case HIGH = 4;
    case URGENTLY = 5;

    public static function getTaskPrioritiesFilterData(): array
    {
        return [
            [
                'id' => self::FOR_FUTURE(),
                'label' => 'for future'
            ],
            [
                'id' => self::LOW(),
                'label' => 'Low'
            ],
            [
                'id' => self::MIDDLE(),
                'label' => 'Middle'
            ],
            [
                'id' => self::HIGH(),
                'label' => 'High'
            ],
            [
                'id' => self::URGENTLY(),
                'label' => 'Urgently'
            ]
        ];
    }

}
