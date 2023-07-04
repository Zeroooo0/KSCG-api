<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'name',
        'is_paid',
        'status',
        'is_submited',
        'membership_price',
        'amount_to_pay'
    ];

    public function clubs() 
    {
        return $this->belongsToMany(Club::class);
    }
    public function competitiorMemberships()
    {
        return $this->hasMany(CompetitorMembership::class);
    }
    public function document()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
