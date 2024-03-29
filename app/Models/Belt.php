<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Belt extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['name', 'hash_color'];

    public function compatitor()
    {
        return $this->hasMany(Compatitor::class, 'belt_id');
    }
    public function category()
    {
        return $this->belonToMany(Category::class, 'belts_categories');
    }
    public function compatitorMembership()
    {
        return $this->hasMany(CompetitorMembership::class);
    }

}
