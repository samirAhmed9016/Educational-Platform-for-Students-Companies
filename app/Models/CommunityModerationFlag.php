<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityModerationFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reason',
        'flaggable_id',
        'flaggable_type',
        'status',
    ];

    public function flaggable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
