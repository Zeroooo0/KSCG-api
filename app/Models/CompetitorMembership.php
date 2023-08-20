<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitorMembership extends Model
{
    use HasFactory;
    protected $fillable = [
        'club_membership_id',
        'belt_id',
        'competitor_id',
        'membership_price',
        'first_membership'
    ];

    public function clubMembership() 
    {
        return $this->belongsTo(ClubMembership::class);
    }
    public function competitor() 
    {
        return $this->belongsTo(Compatitor::class);
    }
    public function document()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
    public function belt()
    {
        return $this->belongsTo(Belt::class);
    }
}
