<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TopicAxisInput extends Model
{
    use HasFactory;

    protected $table = 'topics_axes_inputs';
    protected $fillable = ['topic_axis_id', 'name', 'type'];

    public function topicAxisInputOptions() : HasMany
    {
        return $this->hasMany(TopicAxisInputOption::class);
    }

    public function topicAxis() : BelongsToMany
    {
        return $this->belongsToMany(TopicAxis::class);
    }
}
