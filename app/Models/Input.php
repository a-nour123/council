<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Input extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'type',
        'axis_id',
    ];

    public function axis() : BelongsTo
    {
        return $this->belongsTo(Axis::class);
    }

    public function options() : HasMany
    {
        return $this->hasMany(Option::class);
    }
}
