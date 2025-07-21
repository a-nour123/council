<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoverLetterReport extends Model
{
    use HasFactory;
    protected $table = 'cover_letters';

    protected $fillable = [
        'name',
        'content',
        'topic_id',
    ];

    // Define the relationship with the Topic model
    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }
}
