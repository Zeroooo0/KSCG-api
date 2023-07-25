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
        'membership_price'
    ];

    public function clubMemberships() 
    {
        return $this->belongsTo(ClubMembership::class);
    }
    public function compatitor() 
    {
        return $this->belongsTo(Compatitor::class);
    }
    public function document()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
    public function belts()
    {
        $this->belongsTo(CompetitorMembership::class);
    }
}
