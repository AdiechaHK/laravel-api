<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['post_id', 'body'];

    /**
     * Get the post that owns the comment.
     *
     * @return BelongsTo<Post,self>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}