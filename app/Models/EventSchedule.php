<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'start',
        'end',
        'is_all_day',
        'name',
        'bg_color',
    ];
}
