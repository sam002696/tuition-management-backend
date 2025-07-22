<?php

namespace App\Http\Controllers\TuitionDetails;

use App\Http\Controllers\Controller;
use App\Http\Requests\TuitionDetailsRequest;
use App\Services\ResponseBuilder\ApiResponseService;
use App\Services\TuitionDetails\TuitionDetailsService;
use Exception;
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
}
