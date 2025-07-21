<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultySessionAttendanceInvite extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_session_id',
        'user_id',
        'status',
        'absent_reason',
        'actual_status',
        'taken'
    ];

    public function faculty_session()
    {
        return $this->belongsTo(FacultySession::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
