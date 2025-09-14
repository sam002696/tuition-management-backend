<?php

namespace App\Http\Controllers\ConnectionRequest;

use App\Http\Controllers\Controller;

use App\Services\ConnectionRequest\ConnectionRequestService;
use App\Services\ResponseBuilder\ApiResponseService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ConnectionRequestController extends Controller
{
    protected $connectionService;

    public function __construct(ConnectionRequestService $connectionService)
    {
        $this->connectionService = $connectionService;
    }

    public function findStudent(Request $request)
    {
        try {
            $requestData = $this->connectionService->findStudentByCustomId($request);

            return ApiResponseService::successResponse(
                ['details' => $requestData],
                'Student details fetched successfully',
                201
            );
        } catch (ValidationException $e) {
            return ApiResponseService::handleValidationError($e);
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
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

    public function listConnections(Request $request)
    {
        try {
            $result = $this->connectionService->getFilteredConnections(
                $request->user(),
                $request->query('status'),   //  'pending', 'accepted', 'rejected'
                $request->query('is_active'),      //  'true', 'false', or null
                $request->query('per_page', 10),   // pagination size
                $request->query('search')
            );

            return ApiResponseService::successResponse($result, 'Connection requests fetched successfully');
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


    public function listAllAcceptedActiveConnections(Request $request)
    {
        try {
            $result = $this->connectionService->getAllAcceptedActiveConnections(
                $request->query('per_page', 5),
                $request->query('search')
            );

            return ApiResponseService::successResponse(
                $result,
                'Accepted & active connections fetched successfully'
            );
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }



    public function checkConnectionStatus(Request $request)
    {
        try {
            $status = $this->connectionService->checkConnectionStatus($request);

            return ApiResponseService::successResponse(
                ['status' => $status],
                'Connection status fetched successfully'
            );
        } catch (ValidationException $e) {
            return ApiResponseService::handleValidationError($e);
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }



    public function disconnectStudentConnection($id)
    {
        try {
            $requestData = $this->connectionService->disconnectConnection($id);

            return ApiResponseService::successResponse(
                ['connection' => $requestData],
                'Connection disconnected successfully.'
            );
        } catch (ValidationException $e) {
            return ApiResponseService::handleValidationError($e);
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }


    public function countConnection()
    {
        try {
            $requestData = $this->connectionService->getConnectionCounts();

            return ApiResponseService::successResponse(
                ['connection_count' => $requestData],
                'Connection count successfully.'
            );
        } catch (ValidationException $e) {
            return ApiResponseService::handleValidationError($e);
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }


    // GET /connections/{id}
    public function show($id)
    {
        try {
            $connection = $this->connectionService->getMineById((int) $id);

            return ApiResponseService::successResponse(
                ['connection' => $connection],
                'Connection request fetched successfully'
            );
        } catch (ModelNotFoundException $e) {
            return ApiResponseService::errorResponse('Connection not found.', 404);
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }
}
