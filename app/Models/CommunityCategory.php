<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'access_role'
    ];

    public function posts()
    {
        return $this->hasMany(CommunityPost::class, 'category_id');
    }
}
