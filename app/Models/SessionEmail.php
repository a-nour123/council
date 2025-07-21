<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SessionEmail extends Model
{
    use HasFactory;

    protected $fillable = ["session_id", "name" ,"email","user_id"];

    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(Session::class, 'sessions');
    }
}
