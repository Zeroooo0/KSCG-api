<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'compatition_id',
        'club_id',
        'compatitor_id',
        'category_id',
        'team_id',
        'team_or_single',
        'status',
        'kata_or_kumite',
        'position',
        'is_printed'
    ];

    public function compatition()
    {
        return $this->belongsTo(Compatition::class);
    }
    public function compatitor()
    {
        return $this->belongsTo(Compatitor::class);
    }
    public function club()
    {
        return $this->belongsTo(Club::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    
}
