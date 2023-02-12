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
        'host_club'
    ];
    public function categories() 
    {
        return $this->belongsToMany(Category::class);
    }
    public function roles() 
    {
        return $this->morphMany(Roles::class, 'roleable');
    }
}
