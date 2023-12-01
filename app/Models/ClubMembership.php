<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'type',
        'name',
        'is_paid',
        'status',
        'is_submited',
        'membership_price',
        'amount_to_pay',
        'address', 
        'start_date'
    ];

    public function club() 
    {
        return $this->belongsTo(Club::class);
    }
    public function competitorMemberships()
    {
        return $this->hasMany(CompetitorMembership::class);
    }
    public function document()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
    public function role()
    {
        return $this->morphMany(Roles::class, 'roleable');
    }
}
