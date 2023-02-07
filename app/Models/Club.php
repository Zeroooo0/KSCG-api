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
        'user_id'
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
    public function specialPersonals() 
    {
        return $this->morphToMany(Image::class, 'imageable');
    }

}
