<?php

namespace App\Models;

use App\Enums\VoteType;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $fillable = [
        'votable_type',
        'votable_id',
        'vote',
    ];

    protected $casts = [
        'vote' => VoteType::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function votable()
    {
        return $this->morphTo();
    }
}
