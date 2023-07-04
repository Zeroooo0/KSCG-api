<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'country',
        'town',
        'address',
        'pib',
        'email',
        'phone_number',
        'user_id',
        'status'
    ];

    public function user() 
    {
        return $this->belongsTo(User::class);
    }
    
    public function compatitors() 
    {
        return $this->hasMany(Compatitor::class);
    }
    public function image() 
    {
        return $this->morphOne(Image::class, 'imageable');
    }
    public function roles() 
    {
        return $this->morphMany(Roles::class, 'roleable');
    }
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }
    public function clubMembership() 
    {
        return $this->hasMany(CompetitorMembership::class);
    }
        
   
}
