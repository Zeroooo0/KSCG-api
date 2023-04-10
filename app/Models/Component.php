<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'type',
        'order_number'
    ];
    public function componentable()
    {
        return $this->morphTo();
    }
    public function images() 
    {
        return $this->morphMany(Image::class, 'imageable');
    }
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
    public function roles() 
    {
        return $this->morphMany(Roles::class, 'roleable');
    }

}
