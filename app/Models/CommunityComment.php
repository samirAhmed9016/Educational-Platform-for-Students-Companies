<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityComment extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'post_id',
        'parent_comment_id',
        'content',
        'is_hidden',
    ];

    // Relations

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(CommunityPost::class, 'post_id');
    }

    public function parent()
    {
        return $this->belongsTo(CommunityComment::class, 'parent_comment_id');
    }

    public function children()
    {
        return $this->hasMany(CommunityComment::class, 'parent_comment_id');
    }


    // public function parent()
    // {
    //     return $this->belongsTo(CommunityComment::class, 'parent_id');
    // }

    // public function children()
    // {
    //     return $this->hasMany(CommunityComment::class, 'parent_id');
    // }

    // public function likes()
    // {
    //     return $this->morphMany(CommunityLike::class, 'likeable');
    // }

    // public function flags()
    // {
    //     return $this->morphMany(CommunityModerationFlag::class, 'flaggable');
    // }
}
