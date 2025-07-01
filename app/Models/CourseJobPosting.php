<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseJobPosting extends Model
{
    use HasFactory;

    protected $table = 'course_job_posting';

    protected $fillable = [
        'job_posting_id',
        'course_id',
    ];

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
