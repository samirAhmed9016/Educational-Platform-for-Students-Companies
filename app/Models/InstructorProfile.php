<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstructorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'skills',
        'education_background',
        'years_of_experience',
        'linkedin_url',
        'portfolio_url',
        'certification_file',
    ];

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
