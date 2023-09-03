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
        'title',
        'user_id'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function image() 
    {
        return $this->morphOne(Image::class, 'imageable');
    }
    public function document()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
    public function seminarMorphApplications()
    {
        return $this->morphMany(SeminarMorphApplication::class, 'applicable');
    }
    public function specialPersonnelForm() 
    {
        return $this->hasOne(SpecialPersonnelForms::class, 'personnel_id');
    }
}
