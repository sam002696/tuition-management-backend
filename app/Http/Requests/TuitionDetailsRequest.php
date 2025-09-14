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
            'tuition_days_per_week' => 'nullable|integer|between:1,7',
            'hours_per_day'         => 'nullable|integer|between:1,24',
            'days_name'             => 'nullable|array|min:1|max:7',
            'days_name.*'           => 'distinct|string|in:Sun,Mon,Tue,Wed,Thu,Fri,Sat',
            'salary_per_month'      => 'nullable|integer|min:0',
            'starting_month'        => ['nullable', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],

            // Course based
            'total_classes_per_course'      => 'nullable|integer|min:1',
            'hours_per_class'               => 'nullable|numeric|min:0.25',
            'salary_per_subject'            => 'nullable|integer|min:0',
            'total_course_completion_salary' => 'nullable|integer|min:0',
            'duration'                      => 'nullable|string|max:255',
        ];
    }


    /**
     * Normalize before validation:
     * - Dedupe + whitelist + stable order (Sun..Sat) for days_name
     * - Auto-fill tuition_days_per_week if missing
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('days_name')) {
            $allowed = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            $input   = (array) $this->input('days_name', []);
            $set     = [];

            foreach ($input as $d) {
                $d = is_string($d) ? trim($d) : $d;
                if (in_array($d, $allowed, true)) {
                    $set[$d] = true; // dedupe
                }
            }

            $normalized = array_values(array_intersect($allowed, array_keys($set)));

            $merge = ['days_name' => $normalized];

            if (!$this->filled('tuition_days_per_week')) {
                $merge['tuition_days_per_week'] = $normalized ? count($normalized) : null;
            }

            $this->merge($merge);
        }
    }

    /**
     * Cross-field checks after base rules.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $type = $this->input('tuition_type');

            if ($type === 'monthly_based') {
                $days = (array) $this->input('days_name', []);
                if (empty($days)) {
                    $v->errors()->add('days_name', 'At least one day must be selected for monthly-based tuition.');
                }

                if ($this->filled('tuition_days_per_week')) {
                    $dpw = (int) $this->input('tuition_days_per_week');
                    if ($dpw !== count(array_unique($days))) {
                        $v->errors()->add(
                            'tuition_days_per_week',
                            'Days per week must match the number of selected days.'
                        );
                    }
                }
            }
        });
    }
}
