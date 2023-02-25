<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeTable extends Model
{
    use HasFactory;

    public function compatition()
    {
        return $this->belongsTo(Compatition::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
