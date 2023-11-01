<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $primaryKey = 'id_rekomendasi';

    public function alternative()
    {
        return $this->belongsTo(Alternative::class);
    }
}
