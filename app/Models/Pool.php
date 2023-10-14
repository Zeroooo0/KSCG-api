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
        'points_reg_one',
        'registration_two',
        'points_reg_two',
        'winner_id',
        'looser_id',
        'start_time',
        'kata_one_id',
        'kata_two_id'
        
    ];

    public function compatition()
    {
        return $this->belongsTo(Compatition::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function officialKata()
    {
        return $this->belongsTo(OfficialKata::class);
    }
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }
    public function kataPointPanel()
    {
        return $this->hasMany(KataPointPanel::class);
    }

}