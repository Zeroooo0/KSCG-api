<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficialKata extends Model
{
    use HasFactory;
    

    protected $filable = [
        'name'
    ];

    public function pools()
    {
        return $this->belongsTo(Pool::class);
    }
    public function poolsTeams()
    {
        return $this->belongsTo(PoolTeam::class);
    }
}
