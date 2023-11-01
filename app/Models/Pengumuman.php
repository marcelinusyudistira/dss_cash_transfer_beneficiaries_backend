<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;

class Pengumuman extends Model
{
    use HasFactory, Sluggable;

    protected $guarded = [];
    protected $primaryKey = 'id_pengumuman';
    protected $table = 'pengumumans';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUpdatedAtAttribute(){
        if(!is_null($this->attributes['updated_at'])){
            return Carbon::parse($this->attributes['updated_at'])->format('d F Y');
        }
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'judul_pengumuman'
            ]
        ];
    }
}
