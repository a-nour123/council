<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionTopic extends Model
{
use HasFactory;

    protected $fillable = [
        'session_id',
        'topic_agenda_id',
        'report_template_content',
        'cover_letter_template_content',
        'escalation_authority',
        'topic_formate'
    ];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function topicAgenda()
    {
        return $this->belongsTo(TopicAgenda::class);
    }
}
