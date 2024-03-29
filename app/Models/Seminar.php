<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seminar extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'deadline',
        'start',
        'address',
        'country',
        'city',
        'host',
        'seminar_type',
        'has_judge',
        'has_compatitor',
        'has_coach',
        'price_judge',
        'price_compatitor',
        'price_coach',
        'is_hidden',
        'image'
    ];
    public function document()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
    public function seminarMorphApplications() 
    {
        return $this->hasMany(SeminarMorphApplication::class);
    }
}
