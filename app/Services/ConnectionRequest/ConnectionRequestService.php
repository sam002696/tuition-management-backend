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
            'tuition_details_id' => 'required|exists:tuition_details,id',
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
            'tuition_details_id' => $validated['tuition_details_id'],
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
            return ConnectionRequest::with(['student', 'tuitionDetails'])
                ->where('teacher_id', $user->id)
                ->where('status', 'pending')
                ->get();
        }

        // fetching requests for students based on their role and status pending
        if ($user->role === 'student') {
            return ConnectionRequest::with(['teacher', 'tuitionDetails'])
                ->where('student_id', $user->id)
                ->where('status', 'pending')
                ->get();
        }

        return collect();
    }

    public function getAllAcceptedActiveConnections(int $perPage = 5, ?string $search = null)
    {
        $user = Auth::user();

        // Base query (accepted + active)
        $query = ConnectionRequest::query()
            ->where('status', 'accepted')
            ->where('is_active', true);

        // Role-based scope + eager loads
        if ($user->role === 'teacher') {
            $query->with(['student', 'tuitionDetails'])
                ->where('teacher_id', $user->id);
        } elseif ($user->role === 'student') {
            $query->with(['teacher', 'tuitionDetails'])
                ->where('student_id', $user->id);
        } else {
            return [
                'requests' => [],
                'pagination' => [
                    'current_page'   => 1,
                    'per_page'       => $perPage,
                    'total'          => 0,
                    'total_pages'    => 0,
                    'has_more_pages' => false,
                ],
            ];
        }

        // Role-aware search by counterparty name
        $search = trim((string) $search);
        if ($search !== '') {
            $relation = $user->role === 'teacher' ? 'student' : 'teacher';
            $query->whereHas($relation, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Paginate (latest first)
        $paginated = $query->latest()->paginate($perPage);

        return [
            'requests' => $paginated->items(),
            'pagination' => [
                'current_page'   => $paginated->currentPage(),
                'per_page'       => $paginated->perPage(),
                'total'          => $paginated->total(),
                'total_pages'    => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
            ],
        ];
    }

    public function getFilteredConnections($user, $status = null, $isActive = null, $perPage = 10, $search = null)
    {
        $query = ConnectionRequest::query();

        // Role-based filtering
        if ($user->role === 'teacher') {
            $query->with(['student', 'tuitionDetails'])->where('teacher_id', $user->id);
        } elseif ($user->role === 'student') {
            $query->with(['teacher', 'tuitionDetails'])->where('student_id', $user->id);
        } else {
            return [
                'requests' => [],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => $perPage,
                    'total' => 0,
                    'total_pages' => 0,
                    'has_more_pages' => false,
                ]
            ];
        }

        // Dynamic filtering
        if (!is_null($status)) {
            $query->where('status', $status);
        }

        if (!is_null($isActive)) {
            $query->where('is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }

        // ---- Dynamic "counterparty name" search ----
        $search = trim((string) $search);
        if ($search !== '') {
            // Teachers search students, students search teachers
            $relation = $user->role === 'teacher' ? 'student' : 'teacher';
            $query->whereHas($relation, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Paginate
        $paginated = $query->latest()->paginate($perPage);

        // Format response
        return [
            'requests' => $paginated->items(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'total_pages' => $paginated->lastPage(),
                'has_more_pages' => $paginated->hasMorePages(),
            ]
        ];
    }

    // check the connection status of teacher student

    public function checkConnectionStatus(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'teacher') {
            abort(409, "Only a teacher can check connection request");
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);

        $connection = ConnectionRequest::where('teacher_id', $user->id)
            ->where('student_id', $validated['student_id'])
            ->latest()
            ->first();

        if (!$connection) {
            abort(404, "Connection not found!");
        }

        return $connection->status;
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


    // get the count of is_active (true) and accepted, pending and is_active(false) and accepted

    public function getConnectionCounts()
    {
        $user = Auth::user();

        if (!in_array($user->role, ['teacher', 'student'])) {
            abort(403, 'Unauthorized user role.');
        }

        $query = ConnectionRequest::query();

        if ($user->role === 'teacher') {
            $query->where('teacher_id', $user->id);
        } else {
            $query->where('student_id', $user->id);
        }

        $activeAcceptedCount = (clone $query)
            ->where('status', 'accepted')
            ->where('is_active', true)
            ->count();

        $inactiveAcceptedCount = (clone $query)
            ->where('status', 'accepted')
            ->where('is_active', false)
            ->count();

        $pendingCount = (clone $query)
            ->where('status', 'pending')
            ->count();

        return [
            'active_accepted' => $activeAcceptedCount,
            'inactive_accepted' => $inactiveAcceptedCount,
            'pending' => $pendingCount,
        ];
    }
}
