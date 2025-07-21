<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgandesTopicForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'agenda_id',
        'content',
    ];
    protected $table = 'agandes_topic_form';

    protected $casts = [
        'content' => 'array',
    ];
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class,'topic_id');
    }

    public function topicAgenda(): BelongsTo
    {
        return $this->belongsTo(TopicAgenda::class,'agenda_id');
    }

}