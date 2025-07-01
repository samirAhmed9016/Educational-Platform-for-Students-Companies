<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'industry',
        'company_description',
        'website_url',
        'contact_person_name',
        'contact_phone',
        'company_size',
    ];

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
