<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'kata_or_kumite',
        'category_name',
        'gender',
        'date_from',
        'date_to',
        'weight_from',
        'weight_to',
        'solo_or_team',
        'status',
        'match_lenght'
    ];
    public function compatitions()
    {
        return $this->belongsToMany(Compatition::class);
    }
    public function belts()
    {
        return $this->belongsToMany(Belt::class, 'belts_categories');
    }
}
