<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyUserDecisionVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'decision_id',
        'status',
    ];

    /**
     * Get the user that owns the vote.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the decision that owns the vote.
     */
    public function decision()
    {
        return $this->belongsTo(FacultySessionDecision::class);
    }
}
