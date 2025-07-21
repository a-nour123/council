<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Department_Council extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','department_id','position_id'];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department() : HasOne
    {
        return $this->hasOne(Department::class);
    }

    public function position() : BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
