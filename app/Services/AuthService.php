<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function attemptLogin(string $username, string $password): ?User
    {
        $user = User::query()->where('username', $username)->first();
        if (!$user) {
            return null;
        }

        if (!Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }
}
