<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeTable extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'compatition_id',
        'categoty_id',
        'tatami_no',
        'order_no',
        'eto_start',
        'eto_finish',
        'status',
        'pairs',
        'started_time',
        'finish_time'
    ];

    public function compatition()
    {
        return $this->belongsTo(Compatition::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
