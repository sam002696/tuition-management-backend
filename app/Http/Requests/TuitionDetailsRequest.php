<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TuitionDetailsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'teacher_id' => 'required|exists:users,id',
            'student_id' => 'required|exists:users,id',
            'tuition_type' => 'required|in:monthly_based,course',
            'class_level' => 'required|string|max:255',
            'subject_list' => 'required|array|min:1',
            'medium' => 'required|string|max:255',
            'institute_name' => 'required|string|max:255',
            'address_line' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'thana' => 'required|string|max:255',
            'study_purpose' => 'nullable|string',

            // Monthly based
            'tuition_days_per_week' => 'nullable|integer',
            'hours_per_day' => 'nullable|integer',
            'days_name' => 'nullable|array',
            'salary_per_month' => 'nullable|integer',
            'starting_month' => 'nullable|string',

            // Course based
            'total_classes_per_course' => 'nullable|integer',
            'hours_per_class' => 'nullable|numeric',
            'salary_per_subject' => 'nullable|integer',
            'total_course_completion_salary' => 'nullable|integer',
            'duration' => 'nullable|string',
        ];
    }
}
