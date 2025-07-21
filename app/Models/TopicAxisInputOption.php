<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopicAxisInputOption extends Model
{
    use HasFactory;

    protected $table = 'topics_axes_inputs_options';

    protected $fillable = ['topic_axis_input_id', 'name'];
}
