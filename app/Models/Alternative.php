<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AlternativeComparison;
use App\Models\AlternativePriority;

class Alternative extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $primaryKey = 'id_alternatif';

    public function alternativeComparison()
    {
        return $this->hasMany(AlternativeComparison::class);
    }

    public function alternativePriority()
    {
        return $this->hasMany(AlternativePriority::class);
    }

    public function recommendation()
    {
        return $this->hasOne(Recommendation::class);
    }
}
