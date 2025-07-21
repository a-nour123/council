<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'order',
        'main_topic_id',
        'classification_reference',
        'escalation_authority',
        'decisions'
    ];

    public function mainTopic() : BelongsTo
    {
        return $this->belongsTo(Topic::class, 'main_topic_id')->whereNull('main_topic_id')->whereNot('id', $this->id);
    }

    public function subTopics() : HasMany
    {
        return $this->hasMany(Topic::class, 'main_topic_id')->whereNotNull('main_topic_id');
    }

    public function axes() : BelongsToMany
    {
        return $this->BelongsToMany(Axis::class, 'topics_axes');
    }

    public function agendas() : HasMany
    {
        return $this->hasMany(TopicAgenda::class, 'topics_agendas');
    }

    // the relation between the topic and the pivot
    public function topicAxes(): HasMany
    {
        return $this->hasMany(TopicAxis::class);
    }
    public function agendastopic(): BelongsToMany
    {
        return $this->belongsToMany(TopicAgenda::class, 'agandes_topic_form');
    }
    
}
