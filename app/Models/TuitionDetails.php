<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TuitionDetails extends Model
{
    protected $fillable = [
        'tuition_type',
        'class_level',
        'subject_list',
        'medium',
        'institute_name',
        'address_line',
        'district',
        'thana',
        'study_purpose',

        // monthly based
        'tuition_days_per_week',
        'hours_per_day',
        'days_name',
        'salary_per_month',
        'starting_month',

        // course based
        'total_classes_per_course',
        'hours_per_class',
        'salary_per_subject',
        'total_course_completion_salary',
        'duration',
    ];

    protected $casts = [
        'subject_list' => 'array',
        'days_name' => 'array',
    ];

    public function connectionRequests()
    {
        return $this->hasMany(ConnectionRequest::class);
    }
}
