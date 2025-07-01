<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    use HasFactory;

    protected $table = 'job_postings';
    protected $fillable = [
        'company_id',
        'title',
        'description',
        'type',
        'location',
        'deadline',
    ];

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_job_posting');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }
}
