<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'agenda_id',
        'file_path',
        'file_name'
    ];

    public function agenda()
    {
        return $this->belongsTo(TopicAgenda::class);
    }
}
