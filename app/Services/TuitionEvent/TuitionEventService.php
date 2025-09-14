<?php

namespace App\Services\TuitionEvent;

use App\Models\ConnectionRequest;
use App\Models\TuitionEvent;
use App\Models\User;
use App\Notifications\TuitionEventNotification;
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
            abort(403, 'Only teachers can create events.');
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            // 'scheduled_at' => 'required|date|after:now',
            'scheduled_at' => 'required|date',
        ]);

        $connection = ConnectionRequest::where([
            ['teacher_id', $user->id],
            ['student_id', $validated['student_id']],
            ['status', 'accepted'],
        ])->first();

        if (!$connection) {
            abort(500, 'Student is not connected or request not accepted.');
        }

        $event =  TuitionEvent::create([
            'teacher_id' => $user->id,
            'student_id' => $validated['student_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'scheduled_at' => $validated['scheduled_at'],
        ]);

        // notify the student

        // finding the student to notify
        $student = User::find($validated['student_id']);

        // Notify the student about the new tuition event
        $student->notify(new TuitionEventNotification([
            'title' => 'New Tuition Event Scheduled',
            'body' => "You have a new tuition event from {$user->name}.",
            'event_id' => $event->id,
        ]));

        return $event;
    }

    public function respond(Request $request, $id)
    {
        $user = Auth::user();

        if ($user->role !== 'student') {
            abort(403, 'Only students can respond to events.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['accepted', 'rejected'])],
        ]);

        $event = TuitionEvent::where('id', $id)
            ->where('student_id', $user->id)
            ->firstOrFail();

        $event->status = $validated['status'];
        $event->save();


        // Notify the teacher about the response

        // finding the teacher to notify
        $teacher = User::find($event->teacher_id);


        // Notify the teacher about the response
        $teacher->notify(new TuitionEventNotification([
            'title' => 'Student Responded to Tuition Event',
            'body' => "{$user->name} has {$validated['status']} your tuition event.",
            'event_id' => $event->id,
        ]));

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


    public function getPendingEvents()
    {

        if (Auth::user()->role === 'teacher') {
            return TuitionEvent::with('student')
                ->where('teacher_id', Auth::id())
                ->where('status', 'pending')
                ->orderBy('scheduled_at', 'asc')
                ->get();
        }

        if (Auth::user()->role === 'student') {
            return TuitionEvent::with('teacher')
                ->where('student_id', Auth::id())
                ->where('status', 'pending')
                ->orderBy('scheduled_at', 'asc')
                ->get();
        }

        return collect();
    }


    public function getEventsForStudentTeacher(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'teacher') {
            abort(403, 'Only teachers can access this route.');
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);

        return TuitionEvent::with('student')
            ->where('teacher_id', $user->id)
            ->where('student_id', $validated['student_id'])
            ->orderBy('scheduled_at', 'asc')
            ->get();
    }


    // get events for a specific teacher for a logged in student
    public function getEventsForTeacherStudent(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'student') {
            abort(403, 'Only students can access this route.');
        }

        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
        ]);

        return TuitionEvent::with('student')
            ->where('student_id', $user->id)
            ->where('teacher_id', $validated['teacher_id'])
            ->orderBy('scheduled_at', 'asc')
            ->get();
    }
}
