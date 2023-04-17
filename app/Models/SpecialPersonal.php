<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialPersonal extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'name',
        'last_name',
        'email',
        'phone_number',
        'role',
        'country',
        'gender',
        'clubId',
        'title'
    ];
    

    public function image() 
    {
        return $this->morphOne(Image::class, 'imageable');
    }
    public function document()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

}
