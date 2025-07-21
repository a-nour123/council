<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'ar_name', 'en_name', 'faculty_id','message'];

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    // public function agenda(): HasMany
    // {
    //     return $this->HasMany(Agenda::class);
    // }
    public function agendas(): HasMany
    {
        return $this->HasMany(TopicAgenda::class);
    }

    public function users() : HasMany
    {
        return $this->hasMany(User::class);
    }

    public function council() : HasOne
    {
        return $this->hasOne(Department_Council::class);
    }

    // calling the head of department (position_id => 3)
    public function HeadOfDepartment(): HasOne
    {
        return $this->hasOne(Department_Council::class)->where('position_id', 3)->with('user');
    }

    // calling the secretary of council department (position_id => 2)
    public function SecretaryOfDepartmentCouncil(): HasOne
    {
        return $this->hasOne(Department_Council::class)->where('position_id', 2)->with('user');
    }

    // calling the members of department (position_id => 1) (acadmic staff)
    public function DepartmentCouncilMembers(): HasOne
    {
        return $this->hasOne(Department_Council::class)->where('position_id', 1)->with('user');
    }

    // getting the faculty id from departments table by department_id
    public function getFacultyId ($departmentId)
    {
        return Department::query()->where('id',$departmentId)->value('faculty_id');
    }

    public function sessions() : HasMany
    {
        return $this->hasMany(Session::class);
    }
}
