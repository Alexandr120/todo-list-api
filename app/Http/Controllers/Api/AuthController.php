<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\UserAuthRepository;
use App\Services\UserAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group API Authenticating requests
 *
 * APIs to user auth
 */
class AuthController extends Controller
{
    /**
     * @var UserAuthRepository
     */
    protected UserAuthRepository $userAuthRepository;

    /**
     * @var UserAuthService
     */
    protected UserAuthService $userAuthService;

    /**
     * @param UserAuthRepository $userAuthRepository
     * @param UserAuthService $userAuthService
     */
    public function __construct(UserAuthRepository $userAuthRepository, UserAuthService $userAuthService)
    {
        $this->middleware('auth:sanctum', ['except' => ['login', 'register']]);

        $this->userAuthRepository = $userAuthRepository;
        $this->userAuthService = $userAuthService;
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
        if ($validator->fails()) return $this->sendValidateErrors('Invalid user data!', $validator->getMessages());

        if (!$this->userAuthRepository->checkCredentials($credentials)) {
            return $this->sendResponse('error', 'Invalid user credentials!', Response::HTTP_OK);
        }

        return $this->sendResponse('success', 'User authorize successfully.', Response::HTTP_OK, [
            'user' => $this->userAuthRepository->user(),
            'authorisation' => [
                'token' => $this->userAuthRepository->createAuthToken(),
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
        $userData = $request->only(['name', 'email', 'password']);

        $validator = \Validator::make($userData, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
        ]);
        if ($validator->fails()) return $this->sendValidateErrors('Invalid user data!', $validator->getMessages());

        try {

            $userData['password'] = Hash::make($userData['password']);

            return $this->sendResponse('success', 'User created successfully!', Response::HTTP_OK, [
                'user' => $this->userAuthService->createUser($userData)
            ]);

        } catch (\Exception $exception) {
            return $this->sendResponse('error', $exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        $this->userAuthRepository->logout();

        return $this->sendResponse('success', 'Logged out successfully.', Response::HTTP_OK);
    }

    /**
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return $this->sendResponse('success', 'Refresh successfully.', Response::HTTP_OK, [
            'user' => $this->userAuthRepository->user(),
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

    /**
     * @param string $message
     * @param array $errors
     * @return JsonResponse
     */
    private function sendValidateErrors(string $message, array $errors): JsonResponse
    {
        return $this->sendResponse('validate error', $message, Response::HTTP_BAD_REQUEST, ['errors' => $errors]);
    }

}
