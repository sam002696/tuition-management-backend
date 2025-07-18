<?php

namespace App\Http\Controllers\TuitionEvent;

use App\Http\Controllers\Controller;

use App\Services\ResponseBuilder\ApiResponseService;
use App\Services\TuitionEvent\TuitionEventService;
use Exception;
use Illuminate\Http\Request;

class TuitionEventController extends Controller
{
    protected $service;

    public function __construct(TuitionEventService $service)
    {
        $this->service = $service;
    }

    public function create(Request $request)
    {
        try {
            $event = $this->service->create($request);

            return ApiResponseService::successResponse(
                ['event' => $event],
                'Event created successfully',
                201
            );
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }

    public function respond(Request $request, $id)
    {
        try {
            $event = $this->service->respond($request, $id);

            return ApiResponseService::successResponse(
                ['event' => $event],
                'Event ' . $event->status
            );
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }

    public function myEvents()
    {
        try {
            $events = $this->service->getMyEvents();

            return ApiResponseService::successResponse(
                ['events' => $events],
                'My events loaded'
            );
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }


    public function myPendingEvents()
    {
        try {
            $events = $this->service->getPendingForStudent();

            return ApiResponseService::successResponse(
                ['events' => $events],
                'Pending events loaded'
            );
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }

}
