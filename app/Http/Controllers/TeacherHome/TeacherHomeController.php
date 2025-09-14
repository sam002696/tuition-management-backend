<?php

namespace App\Http\Controllers\TeacherHome;

use App\Http\Controllers\Controller;
use App\Services\ResponseBuilder\ApiResponseService;
use App\Services\TeacherHome\TeacherHomeService;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Http\Request;


class TeacherHomeController extends Controller
{
    public function __construct(private TeacherHomeService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/teacher/home?date=YYYY-MM-DD
     *
     * Returns the dashboard payload for the authenticated teacher.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->role !== 'teacher') {
                abort(403, 'Only teachers can access this endpoint.');
            }

            $validated = $request->validate([
                'date' => ['nullable', 'date_format:Y-m-d'],
            ]);

            $data = $this->service->dashboard($user, $validated['date'] ?? null);


            return ApiResponseService::successResponse(
                ['teacher_data' => $data],
                'Teacher home data',
                201
            );
        } catch (ValidationException $e) {
            return ApiResponseService::handleValidationError($e);
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }
}
