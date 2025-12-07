<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Notifications\WelcomeEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /**
     * Register a new user and send a welcome email
     *
     * This endpoint handles user registration by validating the input,
     * creating a new user record in the database, and dispatching a
     * queued notification to send a welcome email.
     *
     * @param Request $request - The HTTP request containing user registration data
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(StoreUserRequest $request)
    {
        try {

            $data = $request->validated();
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            // Dispatch the welcome email notification to the queue
            // This ensures that email sending doesn't block the registration response
            $user->notify(new WelcomeEmailNotification());

            // Return success response with user data
            return response()->json([
                'success' => true,
                'message' => 'User registered successfully. A welcome email has been sent.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'created_at' => $user->created_at,
                    ]
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
