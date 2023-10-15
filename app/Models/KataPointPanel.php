<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KataPointPanel extends Model
{
    use HasFactory;

    protected $fillable = [
        'pool_id',
        'registration_id',
        'pool_team_id',
        'team_id',
        'judge',
        'points',
    ];

    protected function pool()
    {
        return $this->belongsTo(Pool::class);
    }

    protected function poolTeam()
    {
        return $this->belongsTo(PoolTeam::class);
    }
}
