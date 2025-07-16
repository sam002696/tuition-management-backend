<?php

namespace App\Services\TuitionEvent;

use App\Models\ConnectionRequest;
use App\Models\TuitionEvent;
use App\Services\ResponseBuilder\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class TuitionEventService
{
    public function create(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'teacher') {
            ApiResponseService::errorResponse(403, 'Only teachers can create events.');
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date|after:now',
        ]);

        $connection = ConnectionRequest::where([
            ['teacher_id', $user->id],
            ['student_id', $validated['student_id']],
            ['status', 'accepted'],
        ])->first();

        if (!$connection) {
            throw ValidationException::withMessages([
                'student_id' => ['Student is not connected or request not accepted.']
            ]);
        }

        return TuitionEvent::create([
            'teacher_id' => $user->id,
            'student_id' => $validated['student_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'scheduled_at' => $validated['scheduled_at'],
        ]);
    }

    public function respond(Request $request, $id)
    {
        $user = Auth::user();

        if ($user->role !== 'student') {
            ApiResponseService::errorResponse(403, 'Only students can respond to events.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['accepted', 'rejected'])],
        ]);

        $event = TuitionEvent::where('id', $id)
            ->where('student_id', $user->id)
            ->firstOrFail();

        $event->status = $validated['status'];
        $event->save();

        return $event;
    }

    public function getMyEvents()
    {
        $user = Auth::user();

        return TuitionEvent::where(function ($query) use ($user) {
            if ($user->role === 'teacher') {
                $query->where('teacher_id', $user->id);
            } else {
                $query->where('student_id', $user->id);
            }
        })->where('status', 'accepted')
            ->orderBy('scheduled_at', 'asc')
            ->get();
    }


    public function getPendingForStudent()
    {
        $user = Auth::user();

        if ($user->role !== 'student') {
            ApiResponseService::errorResponse(403, 'Only students can view pending events.');
        }

        return TuitionEvent::where('student_id', $user->id)
            ->where('status', 'pending')
            ->orderBy('scheduled_at', 'asc')
            ->get();
    }

}
