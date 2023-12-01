<?php

namespace App\Repositories;

interface UserAuthRepositoryInterface
{
    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user(): ?\Illuminate\Contracts\Auth\Authenticatable;

    /**
     * @return bool
     */
    public function checkCredentials(array $credentials): bool;

    /**
     * @return string
     */
    public function createAuthToken(): string;

    /**
     * @return void
     */
    public function logout(): void;
}
