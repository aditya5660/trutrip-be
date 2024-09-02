<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class AuthService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function login(array $credentials)
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user) {
            return ['status' => false, 'message' => 'User not found'];
        }

        if (Auth::attempt($credentials)) {
            $token = $user->createToken('authToken')->plainTextToken;
            return ['status' => true, 'user' => $user, 'token' => $token];
        }

        return ['status' => false, 'message' => 'Login failed'];
    }


}
