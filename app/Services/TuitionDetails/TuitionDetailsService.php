<?php

namespace App\Services\TuitionDetails;

use App\Models\TuitionDetails;
use Illuminate\Http\Request;

class TuitionDetailsService
{
    public function create(Request $request)
    {

        // Check if the same teacher-student pair already has a tuition
        $exists = TuitionDetails::where('teacher_id', $request->teacher_id)
            ->where('student_id', $request->student_id)
            ->exists();

        if ($exists) {
            abort(409, 'Tuition details already exist for this teacher and student.');
        }


        return TuitionDetails::create([
            'teacher_id' => $request->teacher_id,
            'student_id' => $request->student_id,
            'tuition_type' => $request->tuition_type,
            'class_level' => $request->class_level,
            'subject_list' => $request->subject_list,
            'medium' => $request->medium,
            'institute_name' => $request->institute_name,
            'address_line' => $request->address_line,
            'district' => $request->district,
            'thana' => $request->thana,
            'study_purpose' => $request->study_purpose,

            'tuition_days_per_week' => $request->tuition_days_per_week,
            'hours_per_day' => $request->hours_per_day,
            'days_name' => $request->days_name ? $request->days_name : null,
            'salary_per_month' => $request->salary_per_month,
            'starting_month' => $request->starting_month,

            'total_classes_per_course' => $request->total_classes_per_course,
            'hours_per_class' => $request->hours_per_class,
            'salary_per_subject' => $request->salary_per_subject,
            'total_course_completion_salary' => $request->total_course_completion_salary,
            'duration' => $request->duration,
        ]);
    }



    /**
     * Update a single TuitionDetails by its ID.
     * - Allows partial updates (PATCH-friendly).
     * - Prevents duplicate teacher-student pair conflicts if either one changes.
     * - Auto-clears fields that don't apply to the final tuition_type (monthly_based vs course_based).
     */
    public function update(int $id, Request $request)
    {
        $tuition = TuitionDetails::findOrFail($id);

        // Determine the final teacher/student for duplication check
        $teacherId = $request->input('teacher_id', $tuition->teacher_id);
        $studentId = $request->input('student_id', $tuition->student_id);

        $duplicate = TuitionDetails::where('teacher_id', $teacherId)
            ->where('student_id', $studentId)
            ->where('id', '!=', $id)
            ->exists();

        if ($duplicate) {
            abort(409, 'Tuition details already exist for this teacher and student.');
        }

        // Only update fillable fields and only those present in the request
        $payload = $request->only($tuition->getFillable());

        // Figure out what the tuition_type will be after this update
        $finalType = $payload['tuition_type'] ?? $tuition->tuition_type;

        // Normalize mutually-exclusive fields based on the final tuition_type
        if ($finalType === 'monthly_based') {
            // Clear course-based fields
            $payload['total_classes_per_course'] = null;
            $payload['hours_per_class'] = null;
            $payload['salary_per_subject'] = null;
            $payload['total_course_completion_salary'] = null;
            $payload['duration'] = null;
        } elseif ($finalType === 'course_based') {
            // Clear monthly-based fields
            $payload['tuition_days_per_week'] = null;
            $payload['hours_per_day'] = null;
            $payload['days_name'] = null;
            $payload['salary_per_month'] = null;
            $payload['starting_month'] = null;
        }

        $tuition->fill($payload);
        $tuition->save();

        // return $tuition->load(['teacher', 'student']);
    }



    // Fetch a single TuitionDetails by its ID
    public function getById(int $id)
    {
        return TuitionDetails::findOrFail($id);
    }

    // public function getAll()
    // {
    //     return TuitionDetails::with(['teacher', 'student'])->get();
    // }

    public function getByTeacherAndStudent($teacherId, $studentId)
    {
        return TuitionDetails::with(['teacher', 'student'])
            ->where('teacher_id', $teacherId)
            ->where('student_id', $studentId)
            ->firstOrFail();
    }


    // public function delete($id)
    // {
    //     $tuition = TuitionDetails::findOrFail($id);
    //     $tuition->delete();
    //     return $tuition;
    // }
}
