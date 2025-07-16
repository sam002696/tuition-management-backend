<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TuitionEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'student_id',
        'title',
        'description',
        'scheduled_at',
        'status',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
