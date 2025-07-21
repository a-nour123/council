<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionDecision extends Model
{
    use HasFactory;

    protected $fillable = [
        'agenda_id',
        'topic_id',
        'session_id',
        'decision',
        'decision_status',
        'order',
        'approval',
        'decisionChoice',
        'agenda_order',
        'rejected_reason'
    ];

    public function agenda()
    {
        return $this->belongsTo(TopicAgenda::class, 'agenda_id');
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }
    public function votes()
    {
        return $this->hasMany(UserDecisionVote::class, 'decision_id');
    }

    public function scopeTotalApprovedSessionDecision($query)
    {
        return $query->where('approval', 1);
    }
    public function scopeTotalRejectedSessionDecision($query)
    {
        return $query->where('approval', 2);
    }
}
