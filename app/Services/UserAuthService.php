<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Role;

class UserAuthService implements UserAuthServiceInterface
{
    /**
     * @param array $data
     * @return User
     * @throws \Exception
     */
    public function createUser(array $data): User
    {
        $user = User::create($data);
        if (!$user->id) {
            throw new \Exception('Error! User not created!');
        }

        //Default user role after register - "viewer"
        $user->roles()->attach(Role::findByName('viewer', 'api')->id);

        return $user;
    }


}
