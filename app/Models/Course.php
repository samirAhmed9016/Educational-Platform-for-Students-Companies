<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;



    protected $fillable = ['title', 'description', 'price', 'image', 'status', 'category'];


    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function progress()
    {
        return $this->hasMany(Progress::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function studentsCompleted()
    {
        return $this->belongsToMany(User::class, 'course_user')
            ->withPivot('completed_at')
            ->withTimestamps();
    }

    public function jobPostings()
    {
        return $this->belongsToMany(JobPosting::class, 'course_job_posting');
    }



    public function communityPosts()
    {
        return $this->belongsToMany(CommunityPost::class, 'community_post_course');
    }
}
