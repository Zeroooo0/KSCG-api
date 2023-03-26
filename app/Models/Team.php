<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;
    protected $fillable = [
        'compatition_id',
        'name'
    ];

    public function compatition()
    {
        return $this->belongsTo(Compatition::class);
    }
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }
}
