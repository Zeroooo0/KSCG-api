<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pool extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'compatition_id',
        'category_id',
        'pool',
        'group',
        'registration_one',
        'registration_two',
        'winner_id',
        'looser_id',
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
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

}