<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Axis extends Model
{
    use HasFactory;

    protected $table = 'axes';

    protected $casts = [
        'content' => 'array'
    ];

    protected $fillable = ['title', 'content'];


    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class, 'topics_axes');
    }

 
}
