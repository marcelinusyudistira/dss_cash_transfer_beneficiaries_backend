<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CriteriaComparison;
use App\Models\CriteriaPriority;

class Criteria extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $primaryKey = 'id_kriteria';

    public function criteriaComparisons()
    {
        return $this->hasMany(CriteriaComparison::class);
    }

    public function criteriaPriority()
    {
        return $this->hasOne(CriteriaPriority::class);
    }

    public function alternativeComparisons()
    {
        return $this->hasMany(AlternativeComparison::class);
    }

    public function alternativePriority()
    {
        return $this->hasOne(AlternativePriority::class);
    }
}
