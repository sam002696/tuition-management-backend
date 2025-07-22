<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConnectionRequest extends Model
{
    protected $fillable = [
        'tuition_details_id', // Added tuition_details_id field
        'teacher_id',
        'student_id',
        'status',
        'is_active', // Added is_active field
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function tuitionDetails()
    {
        return $this->belongsTo(TuitionDetails::class);
    }
}
