<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    use HasFactory;
    protected $fillable = [
        'special_personals_id',
        'title',
        'role',

    ];

    public function roleable()
    {
        return $this->morphTo();
    }
}
