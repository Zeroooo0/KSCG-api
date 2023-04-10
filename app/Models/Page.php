<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'user_id',
        'cover_image',
        'excerpt',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function images() 
    {
        return $this->morphMany(Image::class, 'imageable');
    }
    public function document()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
    public function components()
    {
        return $this->morphMany(Component::class, 'componentable');
    }
}

