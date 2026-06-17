<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function test()
    {
        $users = User::all();
        return response()->json([
            'status' => 'success',
            'message' => 'Application is working!',
            'users_count' => $users->count(),
            'users' => $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'role' => $user->role,
                ];
            })
        ]);
    }
}