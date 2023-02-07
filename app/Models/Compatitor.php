<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use League\CommonMark\Node\Block\Document;

class Compatitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'kscg_compatitor_id',
        'name',
        'last_name',
        'gender',
        'jmbg',
        'belt',
        'date_of_birth',
        'weight',
        'status'
    ];

    public function club() 
    {
        return $this->belongsTo(Club::class);
    }
    public function image() 
    {
        return $this->morphOne(Image::class, 'imageable');
    }
    public function document()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
