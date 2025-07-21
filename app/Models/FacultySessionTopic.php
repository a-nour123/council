<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultySessionTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_session_id',
        'topic_agenda_id',
        'report_template_content',
        'cover_letter_template_content',
        'escalation_authority',
        'topic_formate',
        'department_id'
    ];

    public function faculty_session()
    {
        return $this->belongsTo(FacultySession::class);
    }

    public function topicAgenda()
    {
        return $this->belongsTo(TopicAgenda::class);
    }
}
