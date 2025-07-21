<?php

namespace App\Services\ConnectionRequest;

use App\Models\ConnectionRequest;
use App\Models\User;
use App\Notifications\ConnectionRequestNotification;
use App\Services\ResponseBuilder\ApiResponseService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ConnectionRequestService
{
    // find student by custom_id only

    public function findStudentByCustomId(Request $request)
    {

        $validated = $request->validate([
            'custom_id' => 'required|exists:users,custom_id',
        ]);

        $teacher = Auth::user();
        if ($teacher->role !== 'teacher') {
            abort(403, 'Only teachers can fetch student info');
        }

        $student = User::where('custom_id', $validated['custom_id'])->firstOrFail();


        return $student;

    }


    public function sendRequest(Request $request)
    {
        $validated = $request->validate([
            'custom_id' => 'required|exists:users,custom_id',
        ]);

        $teacher = Auth::user();
        if ($teacher->role !== 'teacher') {
            abort(403, 'Only teachers can send requests.');
        }

        // Lookup student by custom_id
        $student = User::where('custom_id', $validated['custom_id'])->firstOrFail();

        // Check if already connected (accepted)
        $existingAccepted = ConnectionRequest::where([
            ['teacher_id', $teacher->id],
            ['student_id', $student->id],
            ['status', 'accepted'],
        ])->first();

        if ($existingAccepted) {
            abort(409, 'You are already connected with this student.');
        }

        // Check if there's a pending request
        $existingPending = ConnectionRequest::where([
            ['teacher_id', $teacher->id],
            ['student_id', $student->id],
            ['status', 'pending'],
        ])->first();

        if ($existingPending) {
            abort(409, 'A pending request already exists for this student.');
        }

        // Now safe to create a new request (including if previous was rejected)
        $connection = ConnectionRequest::create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'is_active' => true,
            'status' => 'pending',
        ]);


        // notification trigger here


        // finding the student to notify
        // $student = User::findOrFail($validated['student_id']);

        // Notify the student about the new request
        $student->notify(new ConnectionRequestNotification([
            'title' => 'New Connection Request',
            'body' => "{$teacher->name} sent you a request.",
            'request_id' => $connection->id,
        ]));

        return $connection;
    }

    public function respondToRequest(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['accepted', 'rejected'])],
        ]);

        $student = Auth::user();
        if ($student->role !== 'student') {
            abort(403, 'Only students can respond to requests.');
        }

        $connection = ConnectionRequest::where('id', $id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $connection->update(['status' => $validated['status']]);


        //  notification trigger here


        // finding the teacher to notify
        $teacher = User::findOrFail($connection->teacher_id);


        // Notify the teacher about the response
        $teacher->notify(new ConnectionRequestNotification([
            'title' => "Request {$connection->status}",
            'body' => "{$student->name} has {$connection->status} your connection request.",
            'request_id' => $connection->id,
        ]));

        return $connection;
    }

    public function getUserPendingRequests($user)
    {
        // fetching requests based on user role and status pending
        if ($user->role === 'teacher') {
            return ConnectionRequest::with('student')
                ->where('teacher_id', $user->id)
                ->where('status', 'pending')
                ->get();
        }

        // fetching requests for students based on their role and status pending
        if ($user->role === 'student') {
            return ConnectionRequest::with('teacher')
                ->where('student_id', $user->id)
                ->where('status', 'pending')
                ->get();
        }

        return collect();
    }

    public function getAllAcceptedActiveConnections()
    {
        if (Auth::user()->role === 'teacher') {
            return ConnectionRequest::with('student')
                ->where('teacher_id', Auth::id())
                ->where('status', 'accepted')
                ->where('is_active', true)
                ->get();
        }

        if (Auth::user()->role === 'student') {
            return ConnectionRequest::with('teacher')
                ->where('student_id', Auth::id())
                ->where('status', 'accepted')
                ->where('is_active', true)
                ->get();
        }

        return collect();
    }


    // disconnecting a student connection

    public function disconnectConnection($id)
    {
        $authUser = Auth::user();

        $connection = ConnectionRequest::where('id', $id)
            ->where('status', 'accepted')
            ->where('is_active', true)
            ->first();

        if (!$connection) {
            abort(404, 'Connection not found or already inactive');
        }

        if ($authUser->id !== $connection->teacher_id) {
            abort(403, 'Only the teacher can disconnect the connection');
        }

        $connection->update(['is_active' => false]);

        return $connection;
    }

}
