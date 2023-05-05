<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compatition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country',
        'city',
        'address',
        'start_time_date',
        'registration_deadline',
        'price_single',
        'price_team',
        'status',
        'host_name',
        'registration_status',
        'tatami_no',
        'document',
        'application_limits'
    ];
    public function categories() 
    {
        return $this->belongsToMany(Category::class);
    }
    public function roles() 
    {
        return $this->morphMany(Roles::class, 'roleable');
    }
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }
    public function teams()
    {
        return $this->hasMany(Team::class);
    }
    public function pools()
    {
        return $this->hasMany(Pool::class);
    }
    public function poolsTeam()
    {
        return $this->hasMany(Pool::class);
    }
    public function timeTable()
    {
        return $this->hasMany(TimeTable::class);
    }
    public function image() 
    {
        return $this->morphOne(Image::class, 'imageable');
    }
    public function document()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
