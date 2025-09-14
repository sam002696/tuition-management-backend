<?php

namespace App\Http\Controllers\TuitionDetails;

use App\Http\Controllers\Controller;
use App\Http\Requests\TuitionDetailsRequest;
use App\Services\ResponseBuilder\ApiResponseService;
use App\Services\TuitionDetails\TuitionDetailsService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TuitionDetailsController extends Controller
{
    protected $tuitionService;

    public function __construct(TuitionDetailsService $tuitionService)
    {
        $this->tuitionService = $tuitionService;
    }

    public function store(TuitionDetailsRequest $request)
    {
        try {
            $data = $this->tuitionService->create($request);

            return ApiResponseService::successResponse(
                ['tuition_details' => $data],
                'Tuition details created successfully',
                201
            );
        } catch (ValidationException $e) {
            return ApiResponseService::handleValidationError($e);
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }

    // Update tuition details

    public function update($id, Request $request)
    {
        try {
            $data = $this->tuitionService->update((int) $id, $request);

            return ApiResponseService::successResponse(
                ['tuition_details' => $data],
                'Tuition details updated successfully'
            );
        } catch (ModelNotFoundException $e) {
            return ApiResponseService::errorResponse('Tuition details not found.', 404);
        } catch (ValidationException $e) {
            return ApiResponseService::handleValidationError($e);
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }


    // Fetch tuition details by ID

    public function show($id)
    {
        try {
            $data = $this->tuitionService->getById((int) $id);

            return ApiResponseService::successResponse(
                ['tuition_details' => $data],
                'Tuition details fetched successfully'
            );
        } catch (ModelNotFoundException $e) {
            return ApiResponseService::errorResponse('Tuition details not found.', 404);
        } catch (ValidationException $e) {
            return ApiResponseService::handleValidationError($e);
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }


    public function getByTeacherAndStudent($teacherId, $studentId)
    {
        try {
            $data = $this->tuitionService->getByTeacherAndStudent($teacherId, $studentId);

            return ApiResponseService::successResponse(
                ['tuition_details' => $data],
                'Tuition details fetched successfully'
            );
        } catch (ModelNotFoundException $e) {
            return ApiResponseService::errorResponse('No tuition details found for the given teacher and student.', 404);
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }
}
