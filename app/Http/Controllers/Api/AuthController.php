<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group API Authenticating requests
 *
 * APIs to user auth
 */
class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['login', 'register']]);
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        $validator = \Validator::make($credentials, [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return $this->sendResponse('validate error', 'Invalid user data!', Response::HTTP_BAD_REQUEST, ['errors' => $validator->errors()]);
        }

        if (!Auth::attempt($credentials)) {
            return $this->sendResponse('error', 'Invalid user credentials!', Response::HTTP_BAD_REQUEST);
        }

        $user = Auth::user();
        $token = $user->createToken('token', ['read', 'write'])->plainTextToken;

        return $this->sendResponse('success', 'User authorize successfully.', Response::HTTP_OK, [
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
        ]);
        if ($validator->fails()) {
            return $this->sendResponse('validate error', 'Invalid user data!', Response::HTTP_BAD_REQUEST, ['errors' => $validator->errors()]);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            if (!$user->id) {
                throw new \Exception('Error! User not created!');
            }

            return $this->sendResponse('success', 'User created successfully!', Response::HTTP_OK, ['user' => $user]);

        } catch (\Exception $exception) {
            return $this->sendResponse('error', $exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        Auth::user()->currentAccessToken()->delete();

        return $this->sendResponse('success', 'Logged out successfully.', Response::HTTP_OK);
    }

    /**
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return $this->sendResponse('success', 'Refresh successfully.', Response::HTTP_OK, [
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    /**
     * @param string $status
     * @param string $message
     * @param int $code
     * @param array $otherData
     * @return JsonResponse
     */
    private function sendResponse(string $status, string $message, int $code, array $otherData=[]): JsonResponse
    {
        $responseData = array_merge([
            'status' => $status,
            'message' => $message
        ], $otherData);

        return response()->json($responseData, $code);
    }

}
