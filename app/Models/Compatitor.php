<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Compatitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'kscg_compatitor_id',
        'name',
        'last_name',
        'gender',
        'jmbg',
        'belt_id',
        'date_of_birth',
        'status',
        'country'
    ];

    public function club() 
    {
        return $this->belongsTo(Club::class);
    }
    public function image() 
    {
        return $this->morphOne(Image::class, 'imageable');
    }
    public function document()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
    public function belt()
    {
        return $this->belongsTo(Belt::class);
    }
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }
    public function competitorMembership()
    {
        return $this->hasMany(CompetitorMembership::class);
    }
}
