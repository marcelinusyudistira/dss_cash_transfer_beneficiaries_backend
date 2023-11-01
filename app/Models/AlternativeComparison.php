<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlternativeComparison extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $primaryKey = 'id_perbandinganAlternatif';

    public function criteria()
    {
        return $this->belongsTo(Criteria::class);
    }

    public function alternative()
    {
        return $this->belongsTo(Alternative::class);
    }
}

