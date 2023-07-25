<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeminarMorphApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'seminar_id'
    ];

    public function applicable()
    {
        return $this->morphTo();
    }
    public function seminar()
    {
        return $this->belongsTo(Seminar::class);
    }

}
