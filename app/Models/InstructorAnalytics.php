<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstructorAnalytics extends Model
{
    use HasFactory;
    protected $fillable = ['instructor_id', 'total_courses', 'total_students', 'avg_rating', 'total_earnings'];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
}
