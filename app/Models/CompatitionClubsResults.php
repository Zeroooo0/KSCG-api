<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompatitionClubsResults extends Model
{
    use HasFactory;
    protected $fillable = [
        'compatition_id',
        'club_id',
        'gold_medals',
        'silver_medals',
        'bronze_medals',
        'points',
        'no_compatitors',
        'no_teams',
        'no_singles',
        'total_price'
    ];
    public function club() 
    {
        return $this->belongsTo(Club::class);
    }
    public function compatition() 
    {
        return $this->belongsTo(Compatition::class);
    }
}
