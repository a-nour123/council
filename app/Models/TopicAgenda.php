<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TopicAgenda extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'faculty_id',
        'department_id',
        'created_by',
        'status',
        'topic_id',
        'yearly_calendar_id',
        'note',
        'order',
        'name',
        'classification_reference',
        'escalation_authority',
        'updates'
    ];

    protected $table = 'topics_agendas';

    public function topic()
    {

        return ($this->belongsTo(Topic::class, 'topic_id'));
    }
    public function departement()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
    public function owner()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // this is used to get all topic which status is approved
    public function scopeWithStatus($query)
    {
        return $query->where('status', 1);
    }

    public static function getTopicsWithStatus()
    {
        return self::with('topic')->withStatus()->get();
    }

    public function sessions()
    {
        return $this->belongsToMany(Session::class, 'session_topics');
    }

    public function faculty_sessions()
    {
        return $this->belongsToMany(Session::class, 'faculty_session_topics');
    }
    // -----------//
    // public function topic(): BelongsTo
    // {
    //     return $this->BelongsTo(Topic::class);
    // }

    public function faculty(): BelongsTo
    {
        return $this->BelongsTo(Faculty::class);
    }

    public function departments(): BelongsTo
    {
        return $this->BelongsTo(Department::class);
    }

    public function user(): BelongsTo
    {
        return $this->BelongsTo(User::class);
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class, 'agandes_topic_form', 'agenda_id', 'topic_id');
    }


    public function sessionTopics()
    {
        return $this->hasMany(SessionTopic::class);
    }

    public function facultySessionTopics()
    {
        return $this->hasMany(FacultySessionTopic::class);
    }

    public function academic_year(): BelongsTo
    {
        return ($this->belongsTo(YearlyCalendar::class, 'yearly_calendar_id'));
    }
    public function agendaImages()
    {
        return $this->hasMany(AgendaImage::class);
    }
    public function scopeWithAccepted($query)
    {
        return $query->where('status', 1);
    }
    public function scopeWithRejected($query)
    {
        return $query->where('status', '!=', 0)->where('status', '!=', 1);
    }
    public function scopeWithPending($query)
    {
        return $query->where('status', 0);
    }
}
