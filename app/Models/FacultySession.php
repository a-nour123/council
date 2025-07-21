<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FacultySession extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'place',
        'reject_reason',
        'responsible_id',
        'session_way',
        'status',
        'created_by',
        'start_time',
        'total_hours',
        'scheduled_end_time',
        'actual_end_time',
        'decision_by',
        'department_id',
        'faculty_id',
        'order',
        'yearly_calendar_id'
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function TopicAgenda()
    {
        return $this->belongsToMany(TopicAgenda::class, 'faculty_session_topics');
    }

    // get topic agenda by topic_id
    public function getTopicAgenda($topic_id)
    {
        return TopicAgenda::query()->where('topic_id', $topic_id)->first();
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'faculty_session_users');
    }

    public function facultyEmails()
    {
        return $this->hasMany(FacultySessionEmail::class);
    }
    public function facultySessionDecisions()
    {
        return $this->hasMany(FacultySessionDecision::class, 'faculty_session_id');
    }

    public function facultySessionTopics()
    {
        return $this->hasMany(FacultySessionTopic::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id');
    }

    public function year(): BelongsTo
    {
        return $this->belongsTo(YearlyCalendar::class,'yearly_calendar_id');
    }

    public function scopeTotalPendingSession($query)
    {
        return $query->where('status', 0);
    }
    public function scopeTotalAcceptedSession($query)
    {
        return $query->where('status', 1);
    }
    public function scopeTotalRejectedSession($query)
    {
        return $query->where('status', '!=', 0)->where('status', '!=', 1);
    }
}
