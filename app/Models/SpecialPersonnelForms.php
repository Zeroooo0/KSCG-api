<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialPersonnelForms extends Model
{
    use HasFactory;

    protected $fillable = [
        'personnel_id',
        'form_type',
        'full_name',
        'name_of_parent',
        'jmbg',
        'birth_date',
        'birth_place',
        'address',
        'landline_phone',
        'mob_phone',
        'email',
        'belt',
        'belt_acquired',
        'certificate',
        'certificate_id',
        'certificate_acquired',
        'certificate_issuer',
        'policy_confirmation',
        'club_applying_for',
        'club_last_season',
        'for_categories',
        'judge_title',
        'judge_title_acquired',
    ];
    public function specialPersonal()
    {
        return $this->belongsTo(SpecialPersonal::class);
    }
}
