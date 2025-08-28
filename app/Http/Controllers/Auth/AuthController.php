<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use App\Services\ResponseBuilder\ApiResponseService;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $authService;

    /**
     * Injecting the AuthService into the controller.
     *
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handling user registration request.
     *
     * Validates the incoming request and delegates user creation to AuthService.
     * Returns a structured success response or handles validation/unexpected errors.
     */
    public function register(Request $request)
    {
        try {
            // Delegating registration logic to AuthService
            $user = $this->authService->registerUser($request);

            return ApiResponseService::successResponse(
                ['user' => $user],
                'User registered successfully',
                201
            );
        } catch (ValidationException $e) {
            // Handling validation errors
            return ApiResponseService::handleValidationError($e);
        } catch (Exception $e) {
            // Handling unexpected errors
            return ApiResponseService::handleUnexpectedError($e);
        }
    }

    /**
     * Handling user login request.
     *
     * Validates credentials and generates authentication token if successful.
     * Returns structured response or error if credentials are invalid.
     */
    public function login(Request $request)
    {
        try {
            // Delegating login logic to AuthService
            $authData = $this->authService->loginUser($request);

            if (!$authData) {
                // Handling invalid credentials
                return ApiResponseService::errorResponse(
                    'Invalid email or password',
                    401
                );
            }

            // Returning structured success response
            return ApiResponseService::successResponse(
                $authData,
                'Login successful'
            );
        } catch (ValidationException $e) {
            // Handling validation errors
            return ApiResponseService::handleValidationError($e);
        } catch (Exception $e) {
            // Handling unexpected errors
            return ApiResponseService::handleUnexpectedError($e);
        }
    }


    /**
     * Retrieving the currently authenticated user.
     *
     *
     * Returns user information in a structured format.
     */
    public function user(Request $request)
    {
        return ApiResponseService::successResponse(
            ['user' => $request->user()],
            'User data retrieved'
        );
    }

    public function forgotPassword(Request $request)
    {
        try {
            $this->authService->sendResetLink($request);
            return ApiResponseService::successResponse([], 'If that email exists, a reset link has been sent.');
        } catch (ValidationException $e) {
            return ApiResponseService::handleValidationError($e);
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $this->authService->resetPassword($request);
            return ApiResponseService::successResponse([], 'Password has been reset successfully.');
        } catch (ValidationException $e) {
            return ApiResponseService::handleValidationError($e);
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }
}
