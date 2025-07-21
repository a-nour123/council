<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollegeCouncil extends Model
{
    use HasFactory;

    protected $fillable = ['session_id', 'status','reject_reason','topic_id','escalation_authority'];

    public function session() : BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

}
