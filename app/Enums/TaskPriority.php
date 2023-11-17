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

}
