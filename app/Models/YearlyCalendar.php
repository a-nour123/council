<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class YearlyCalendar extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'start_date', 'end_date','status'];

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function agendas(): HasMany
    {
        return $this->hasMany(TopicAgenda::class);
    }

    public function facultySessions(): HasMany
    {
        return $this->hasMany(FacultySession::class);
    }
}
