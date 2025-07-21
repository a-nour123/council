<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TopicAxis extends Model
{
    use HasFactory;

    protected $fillable = ['topic_id','axis_id'];
    protected $table = 'topics_axes';

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function axis(): BelongsTo
    {
        return $this->belongsTo(Axis::class);
    }


}
