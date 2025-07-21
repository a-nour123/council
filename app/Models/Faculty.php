<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Faculty extends Model
{
    use HasFactory;

    // protected $fillable = ['code', 'ar_name', 'en_name', 'headquarter_id'];
    protected $fillable = ['code', 'ar_name', 'en_name','message'];

    // public function headquarter(): BelongsTo
    // {
    //     return $this->belongsTo(Headquarter::class);
    // }

    public function headquarters(): BelongsToMany
    {
        return $this->belongsToMany(Headquarter::class, 'faculty_headquarter');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    public function agendas(): HasMany
    {
        return $this->HasMany(Agenda::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function council() : HasOne
    {
        return $this->hasOne(FacultyCouncil::class);
    }

    public function facultyDean(): HasOne
    {
        return $this->hasOne(FacultyCouncil::class)->where('position_id', 5)->with('user');
    }

    public function secretaryOfFacultyCouncil(): HasOne
    {
        return $this->hasOne(FacultyCouncil::class)->where('position_id', 4)->with('user');
    }
    public function FacultyCouncilMembers(): HasMany
    {
        return $this->hasMany(FacultyCouncil::class)->whereNotIn('position_id', [4,5])->with('user');
    }

    // getting the headquarter id from faculties table by faculty_id
    // public function getHeadquarterId ($faculty_id)
    // {
    //     return Faculty::query()->where('id',$faculty_id)->value('headquarter_id');
    // }
    public function getHeadquarterIds($faculty_id)
    {
        return DB::table('faculty_headquarter')->where('faculty_id', $faculty_id)->pluck('headquarter_id');
    }

    public function sessions() : HasMany
    {
        return $this->hasMany(FacultySession::class);
    }
}
