<?php

namespace App\Http\Controllers\ConnectionRequest;

use App\Http\Controllers\Controller;

use App\Services\ConnectionRequest\ConnectionRequestService;
use App\Services\ResponseBuilder\ApiResponseService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ConnectionRequestController extends Controller
{
    protected $connectionService;

    public function __construct(ConnectionRequestService $connectionService)
    {
        $this->connectionService = $connectionService;
    }

    public function send(Request $request)
    {
        try {
            $requestData = $this->connectionService->sendRequest($request);

            return ApiResponseService::successResponse(
                ['connection' => $requestData],
                'Connection request sent successfully',
                201
            );
        } catch (ValidationException $e) {
            return ApiResponseService::handleValidationError($e);
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }

    public function respond(Request $request, $id)
    {
        try {
            $requestData = $this->connectionService->respondToRequest($request, $id);

            return ApiResponseService::successResponse(
                ['connection' => $requestData],
                'Request ' . $requestData->status
            );
        } catch (ValidationException $e) {
            return ApiResponseService::handleValidationError($e);
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }

    public function listMyPendingConnections(Request $request)
    {
        try {
            $requests = $this->connectionService->getUserPendingRequests($request->user());

            return ApiResponseService::successResponse(
                ['requests' => $requests],
                'Connection requests fetched'
            );
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }


    public function listAllAcceptedConnections(Request $request)
    {
        try {
            $requests = $this->connectionService->getAllAcceptedConnections();

            return ApiResponseService::successResponse(
                ['requests' => $requests],
                'All connection requests fetched'
            );
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }
}
