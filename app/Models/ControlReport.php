<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControlReport extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'content',
        'topic_id',
        'topic_formate'
    ];

    protected $table = 'control_reports';

    public function topicReport()
    {
        return $this->belongsTo(Topic::class, 'topic_id');
    }


}
