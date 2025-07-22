<?php

namespace App\Services\TuitionDetails;

use App\Models\TuitionDetails;
use Illuminate\Http\Request;

class TuitionDetailsService
{
    public function create(Request $request)
    {
        return TuitionDetails::create([
            'teacher_id' => $request->teacher_id,
            'student_id' => $request->student_id,
            'tuition_type' => $request->tuition_type,
            'class_level' => $request->class_level,
            'subject_list' => json_encode($request->subject_list),
            'medium' => $request->medium,
            'institute_name' => $request->institute_name,
            'address_line' => $request->address_line,
            'district' => $request->district,
            'thana' => $request->thana,
            'study_purpose' => $request->study_purpose,

            'tuition_days_per_week' => $request->tuition_days_per_week,
            'hours_per_day' => $request->hours_per_day,
            'days_name' => $request->days_name ? json_encode($request->days_name) : null,
            'salary_per_month' => $request->salary_per_month,
            'starting_month' => $request->starting_month,

            'total_classes_per_course' => $request->total_classes_per_course,
            'hours_per_class' => $request->hours_per_class,
            'salary_per_subject' => $request->salary_per_subject,
            'total_course_completion_salary' => $request->total_course_completion_salary,
            'duration' => $request->duration,
        ]);
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
