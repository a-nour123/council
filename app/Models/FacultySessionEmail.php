<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FacultySessionEmail extends Model
{
    use HasFactory;

    protected $fillable = ["faculty_session_id", "name" ,"email","user_id"];

    public function facultySessions(): BelongsToMany
    {
        return $this->belongsToMany(FacultySession::class, 'faculty_sessions');
    }
}
