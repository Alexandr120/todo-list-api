<?php

namespace App\Services;

use App\Models\User;

interface UserAuthServiceInterface
{
    /**
     * @return User
     */
    public function createUser(array $data): User;
}
