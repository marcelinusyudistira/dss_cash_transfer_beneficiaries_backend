<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Saran extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $primaryKey = 'id_saran';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUpdatedAtAttribute(){
        if(!is_null($this->attributes['updated_at'])){
            return Carbon::parse($this->attributes['updated_at'])->format('d F Y');
        }
    }
}
