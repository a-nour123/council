<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Session extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'place',
        'reject_reason',
        'responsible_id',
        'topic_id',
        'status',
        'created_by',
        'start_time',
        'total_hours',
        'scheduled_end_time',
        'actual_end_time',
        'decision_by',
        'department_id',
        'order',
        'yearly_calendar_id'
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function topicAgenda()
    {
        return $this->belongsToMany(TopicAgenda::class, 'session_topics')
            ->withPivot('report_template_content ');
    }

    // public function TopicAgenda()
    // {
    //     return $this->belongsTo(TopicAgenda::class, 'topic_id')->where('status', '=', 1);
    // }


    // get topic agenda by topic_id
    public function getTopicAgenda($topic_id)
    {
        return TopicAgenda::query()->where('topic_id', $topic_id)->first();
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    public function year(): BelongsTo
    {
        return $this->belongsTo(YearlyCalendar::class,'yearly_calendar_id');
    }
    // public function topic()
    // {
    //     return $this->belongsTo(Topic::class);
    // }
    // protected static function boot()
    // {
    //     parent::boot();

    //     static::saving(function ($model) {
    //         if (is_array($model->responsible_id)) {
    //             $model->responsible_id = implode(',', $model->responsible_id);
    //         }
    //     });
    // }

    // public function getResponsibleIdAttribute($value)
    // {
    //     return explode(',', $value);
    // }

    // public function getResponsibleNamesAttribute()
    // {
    //     $responsibleIds = $this->responsible_id;
    //     if (!is_array($responsibleIds)) {
    //         $responsibleIds = explode(',', $responsibleIds);
    //     }
    //     return User::whereIn('id', $responsibleIds)->pluck('name')->implode(', ');
    // }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'session_user');
    }

    public function collegeCouncils()
    {
        return $this->HasMany(Session::class);
    }

    public function emails(): BelongsToMany
    {
        return $this->belongsToMany(SessionEmail::class, 'session_emails');
    }
    public function sessionDecisions()
    {
        return $this->hasMany(SessionDecision::class, 'session_id');
    }

    public function sessionTopics()
    {
        return $this->hasMany(SessionTopic::class);
    }
    public function sessionEmails()
    {
        return $this->hasMany(SessionEmail::class);
    }
    public function attendances()
    {
        return $this->hasMany(SessionAttendanceInvite::class);
    }
    public function topics()
    {
        return $this->belongsToMany(TopicAgenda::class, 'session_topics', 'session_id', 'topic_agenda_id');
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
