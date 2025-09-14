<?php

namespace App\Services\TuitionDetails;

use App\Models\TuitionDetails;
use Illuminate\Http\Request;

class TuitionDetailsService
{
    public function create(Request $request)
    {
        // Preventing duplicate teacher-student pair
        $exists = TuitionDetails::where('teacher_id', $request->teacher_id)
            ->where('student_id', $request->student_id)
            ->exists();

        if ($exists) {
            abort(409, 'Tuition details already exist for this teacher and student.');
        }

        $days = $this->normalizeDays($request->input('days_name'));

        return TuitionDetails::create([
            'teacher_id'     => $request->teacher_id,
            'student_id'     => $request->student_id,
            'tuition_type'   => $request->tuition_type,
            'class_level'    => $request->class_level,
            'subject_list'   => $request->subject_list,
            'medium'         => $request->medium,
            'institute_name' => $request->institute_name,
            'address_line'   => $request->address_line,
            'district'       => $request->district,
            'thana'          => $request->thana,
            'study_purpose'  => $request->study_purpose,

            // monthly based
            'tuition_days_per_week' => $request->tuition_days_per_week ?? ($days ? count($days) : null),
            'hours_per_day'         => $request->hours_per_day,
            'days_name'             => $days,
            'salary_per_month'      => $request->salary_per_month,
            'starting_month'        => $request->starting_month,

            // course based
            'total_classes_per_course'      => $request->total_classes_per_course,
            'hours_per_class'               => $request->hours_per_class,
            'salary_per_subject'            => $request->salary_per_subject,
            'total_course_completion_salary' => $request->total_course_completion_salary,
            'duration'                      => $request->duration,
        ]);
    }

    /**
     * Update a single TuitionDetails by its ID.
     * - Allows partial updates (PATCH-friendly).
     * - Prevents duplicate teacher-student pair conflicts if either one changes.
     * - Auto-clears fields that don't apply to the final tuition_type (monthly_based vs course).
     */
    public function update(int $id, Request $request)
    {
        $tuition = TuitionDetails::findOrFail($id);

        // Check duplicate pair if teacher/student changed
        $teacherId = $request->input('teacher_id', $tuition->teacher_id);
        $studentId = $request->input('student_id', $tuition->student_id);

        $duplicate = TuitionDetails::where('teacher_id', $teacherId)
            ->where('student_id', $studentId)
            ->where('id', '!=', $id)
            ->exists();

        if ($duplicate) {
            abort(409, 'Tuition details already exist for this teacher and student.');
        }

        // Only updating fillable fields
        $payload = $request->only($tuition->getFillable());

        if ($request->has('days_name')) {
            $payload['days_name'] = $this->normalizeDays($request->input('days_name'));
            if (!$request->has('tuition_days_per_week')) {
                $payload['tuition_days_per_week'] = $payload['days_name'] ? count($payload['days_name']) : null;
            }
        }

        // Determine final type after update
        $finalType = $payload['tuition_type'] ?? $tuition->tuition_type;

        // Normalize mutually-exclusive fields
        if ($finalType === 'monthly_based') {
            // Clear course-based fields
            $payload['total_classes_per_course']       = null;
            $payload['hours_per_class']                = null;
            $payload['salary_per_subject']             = null;
            $payload['total_course_completion_salary'] = null;
            $payload['duration']                       = null;
        } elseif ($finalType === 'course') {
            // Clear monthly-based fields
            $payload['tuition_days_per_week'] = null;
            $payload['hours_per_day']         = null;
            $payload['days_name']             = null;
            $payload['salary_per_month']      = null;
            $payload['starting_month']        = null;
        }

        $tuition->fill($payload);
        $tuition->save();
    }

    // Fetch a single TuitionDetails by its ID
    public function getById(int $id)
    {
        return TuitionDetails::findOrFail($id);
    }

    public function getByTeacherAndStudent($teacherId, $studentId)
    {
        return TuitionDetails::with(['teacher', 'student'])
            ->where('teacher_id', $teacherId)
            ->where('student_id', $studentId)
            ->firstOrFail();
    }

    /**
     * Canonicalize days: dedupe, whitelist, stable order Sun..Sat.
     */
    private function normalizeDays(?array $days): ?array
    {
        if (!$days) return null;

        $allowed = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $set = [];
        foreach ($days as $d) {
            $d = is_string($d) ? trim($d) : $d;
            if (in_array($d, $allowed, true)) {
                $set[$d] = true;
            }
        }
        return array_values(array_intersect($allowed, array_keys($set)));
    }
}
