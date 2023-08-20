<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoolTeam extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'compatition_id',
        'category_id',
        'pool_type',
        'pool',
        'group',
        'team_one',
        'team_two',
        'winner_id',
        'looser_id',
        'status',
        'start_time'
    ];

    public function compatition()
    {
        return $this->belongsTo(Compatition::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function teams()
    {
        return $this->hasMany(Team::class);
    }

}
