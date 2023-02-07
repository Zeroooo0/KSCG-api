<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documents extends Model
{
    use HasFactory;
    
    protected $filable = [
        'name',
        'doc_link'
    ];

    public function documentable()
    {
        return $this->morphTo();
    }
}
