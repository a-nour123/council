<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultySessionDecision extends Model
{
    use HasFactory;

    protected $fillable = [
        'agenda_id',
        'topic_id',
        'faculty_session_id',
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

    public function college_councils()
    {
        return $this->belongsTo(CollegeCouncil::class);
    }

    public function facultySessions()
    {
        return $this->belongsTo(FacultySession::class,'faculty_session_id');
    }

    public function votes()
    {
        return $this->hasMany(FacultyUserDecisionVote::class, 'decision_id');
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
