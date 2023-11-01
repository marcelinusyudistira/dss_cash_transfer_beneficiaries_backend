<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Criteria;

class CriteriaComparison extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $primaryKey = 'id_perbandinganKriteria';

    public function criteria()
    {
        return $this->belongsTo(Criteria::class);
    }
}
