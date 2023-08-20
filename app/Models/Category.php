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
        'solo_or_team',
        'status',
        'match_lenght',
        'years_from',
        'years_to',
        'repesaz',
        'is_official'
    ];
    public function compatitions()
    {
        return $this->belongsToMany(Compatition::class);
    }
    public function belts()
    {
        return $this->belongsToMany(Belt::class, 'belts_categories');
    }
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }
    public function pools()
    {
        return $this->hasMany(Pool::class);
    }
    public function timeTable()
    {
        return $this->hasMany(TimeTable::class);
    }
}
