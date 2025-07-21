<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControlReportFaculty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'content',
        'topic_id',
        'topic_formate',
    ];

    protected $table = 'control_report_faculties';

    // Define the relationship with the Topic model
    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }
}
