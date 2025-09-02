<?php

namespace App\Services\TeacherHome;

use App\Models\User;
use App\Models\TuitionEvent;
use App\Models\TuitionDetails;
use App\Models\ConnectionRequest;
use Carbon\Carbon;

class TeacherHomeService
{

    public function dashboard(User $teacher, ?string $dateYmd = null): array
    {
        $tz   = config('app.timezone');
        $now  = Carbon::now($tz);
        $day  = $dateYmd ? Carbon::createFromFormat('Y-m-d', $dateYmd, $tz) : $now->copy();
        $from = $day->copy()->startOfDay();
        $to   = $day->copy()->endOfDay();

        // ----- Today’s events (for schedule / next class / attendance) -----
        $eventsToday = TuitionEvent::with('student:id,name,custom_id')
            ->where('teacher_id', $teacher->id)
            ->whereBetween('scheduled_at', [$from, $to])
            ->orderBy('scheduled_at')
            ->get();

        // Preload tuition details for duration mapping (avoid N+1)
        $studentIds = $eventsToday->pluck('student_id')->unique()->all();
        $detailsByStudent = TuitionDetails::where('teacher_id', $teacher->id)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->keyBy('student_id');

        // ----- Stats: total students (active/accepted), new today, total classes, classes today -----
        $activeAccepted = ConnectionRequest::query()
            ->where('teacher_id', $teacher->id)
            ->where('status', 'accepted')
            ->where('is_active', 1);

        $studentsTotal = (clone $activeAccepted)->distinct('student_id')->count('student_id');
        $newStudentsToday = (clone $activeAccepted)
            ->whereBetween('updated_at', [$from, $to])
            ->distinct('student_id')->count('student_id');

        $classesTotal = TuitionDetails::where('teacher_id', $teacher->id)->count();
        $classesToday = $eventsToday->count();
        $studentsToday = $eventsToday->pluck('student_id')->unique()->count();

        // ----- Pending actions badge (your “Assignments” pill replacement) -----
        $pendingRequests = ConnectionRequest::where('teacher_id', $teacher->id)
            ->where('status', 'pending')
            ->count();

        // ----- Attendance (today): completed/started out of sessions that should have happened by now -----
        $eligibleByNow = $eventsToday->filter(fn($e) => Carbon::parse($e->scheduled_at, $tz)->lte($now))->count();
        $attendedByNow = $eventsToday->filter(fn($e) => in_array($e->status, ['started', 'completed'], true))->count();
        $attendanceRate = $eligibleByNow > 0 ? round(($attendedByNow / $eligibleByNow) * 100, 1) : null; // % or null

        // ----- Next class (today) -----
        $nextEvent = $eventsToday->firstWhere(fn($e) => Carbon::parse($e->scheduled_at, $tz)->gt($now));
        $nextStartsInMin = $nextEvent
            ? $now->diffInMinutes(Carbon::parse($nextEvent->scheduled_at, $tz), false)
            : null;

        // ----- Schedule list (today) -----
        $schedule = $eventsToday->map(function (TuitionEvent $e) use ($tz, $now, $detailsByStudent) {
            $startsAt = Carbon::parse($e->scheduled_at, $tz);
            $durationMin = $this->durationFor($detailsByStudent->get($e->student_id)); // fallback inside
            $label = $this->statusLabel($e, $startsAt, $now);
            $joinNow = $this->canJoinNow($e, $startsAt, $now);

            return [
                'id'              => $e->id,
                'title'           => $e->title,
                'student'         => [
                    'id'        => $e->student?->id,
                    'name'      => $e->student?->name,
                    'custom_id' => $e->student?->custom_id,
                ],
                'starts_at_iso'   => $startsAt->toIso8601String(),
                'starts_at_human' => $startsAt->format('g:i A'),
                'duration_min'    => $durationMin,
                'status'          => $label,      // 'live' | 'starting_soon' | 'upcoming' | 'completed' | 'overdue'
                'join_now'        => $joinNow,    // bool: within 15m or already 'started'
            ];
        })->values();

        return [
            'meta' => [
                'date'     => $day->toDateString(),
                'now_iso'  => $now->toIso8601String(),
                'timezone' => $tz,
            ],
            'overview' => [
                'sessions_today'   => $classesToday,   // “3 Classes”
                'students_today'   => $studentsToday,  // “16 Students”
                'attendance_rate'  => $attendanceRate, // percent or null
                'pending_requests' => $pendingRequests,
                'next_class'       => $nextEvent ? [
                    'id'                => $nextEvent->id,
                    'title'             => $nextEvent->title,
                    'starts_at_iso'     => Carbon::parse($nextEvent->scheduled_at, $tz)->toIso8601String(),
                    'starts_in_minutes' => $nextStartsInMin,
                ] : null,
            ],
            'stats' => [
                'students_total'    => $studentsTotal,   // big “Students” tile
                'new_students_today' => $newStudentsToday, // “+2 today”
                'classes_total'     => $classesTotal,     // big “Classes” tile
                'classes_today'     => $classesToday,     // “3 today”
                // revenue/completion intentionally omitted
            ],
            'schedule_today' => $schedule,
        ];
    }

    /**
     * Computing a duration (minutes) from TuitionDetails; default to 60 if unknown.
     */
    private function durationFor(?TuitionDetails $td): int
    {
        if (!$td) {
            return 60;
        }
        if ($td->tuition_type === 'monthly_based') {
            $hours = (float) ($td->hours_per_day ?? 1);
            return max(30, (int) round($hours * 60));
        }
        // course based
        $hours = (float) ($td->hours_per_class ?? 1);
        return max(30, (int) round($hours * 60));
    }

    /**
     * Status label for chips.
     */
    private function statusLabel(TuitionEvent $e, Carbon $startsAt, Carbon $now): string
    {
        if ($e->status === 'started')   return 'live';
        if ($e->status === 'completed') return 'completed';

        $diff = $now->diffInMinutes($startsAt, false); // negative if in past
        if ($diff <= -5) return 'overdue';
        if ($diff <= 30 && $diff > 0) return 'starting_soon';
        return 'upcoming';
    }

    /**
     * Join-now rule: within 15 minutes of start or already started.
     */
    private function canJoinNow(TuitionEvent $e, Carbon $startsAt, Carbon $now): bool
    {
        if ($e->status === 'started') return true;
        $diff = $now->diffInMinutes($startsAt, false);
        return $diff >= 0 && $diff <= 15;
    }
}
