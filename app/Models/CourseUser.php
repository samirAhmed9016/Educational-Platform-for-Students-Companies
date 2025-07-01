<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseUser extends Model
{
    use HasFactory;


    protected $table = 'course_user';

    protected $fillable = [
        'user_id',
        'course_id',
        'completed_at',
    ];

    protected $dates = [
        'completed_at',
        'created_at',
        'updated_at',
    ];

    public $timestamps = true;


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
