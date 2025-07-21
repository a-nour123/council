<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agenda extends Model
{
    use HasFactory;
    protected $table = 'topics_agendas';

    protected $fillable = [
        'topic_id',
        'faculty_id',
        'department_id',
        'created_by',
        'status',
    ];

    public function topic(): BelongsTo
    {
        return $this->BelongsTo(Topic::class);
    }

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
    // -----------//
    // public function topic(): BelongsTo
    // {
    //     return $this->BelongsTo(Topic::class);
    // }



    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class, 'agandes_topic_form');
    }

}
