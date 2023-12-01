<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Auth;

class UserAuthRepository implements UserAuthRepositoryInterface
{
    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return Auth::user();
    }

    /**
     * @param array $credentials
     * @return bool
     */
    public function checkCredentials(array $credentials): bool
    {
        return Auth::attempt($credentials);
    }

    /**
     * @return string
     */
    public function createAuthToken(): string
    {
        return $this->user()->createToken('token')->plainTextToken;
    }

    /**
     * @return void
     */
    public function logout(): void
    {
        $this->user()->currentAccessToken()->delete();
    }


}
