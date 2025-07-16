<?php

namespace App\Services\ConnectionRequest;

use App\Models\ConnectionRequest;
use App\Services\ResponseBuilder\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ConnectionRequestService
{
    public function sendRequest(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);

        $teacher = Auth::user();
        if ($teacher->role !== 'teacher') {
            ApiResponseService::errorResponse(403, 'Only teachers can send requests.');
        }

        $connection = ConnectionRequest::firstOrCreate([
            'teacher_id' => $teacher->id,
            'student_id' => $validated['student_id'],
        ]);

        //  TODO:  notification trigger here
        return $connection;
    }

    public function respondToRequest(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['accepted', 'rejected'])],
        ]);

        $student = Auth::user();
        if ($student->role !== 'student') {
            ApiResponseService::errorResponse(403, 'Only students can respond to requests.');
        }

        $connection = ConnectionRequest::where('id', $id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $connection->update(['status' => $validated['status']]);

        // TODO:  notification trigger here
        return $connection;
    }

    public function getUserRequests($user)
    {
        if ($user->role === 'teacher') {
            return ConnectionRequest::with('student')->where('teacher_id', $user->id)->get();
        }

        if ($user->role === 'student') {
            return ConnectionRequest::with('teacher')->where('student_id', $user->id)->get();
        }

        return collect();
    }
}
