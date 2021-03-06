<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'type'];

    public function getRouteKeyName(){
        return 'code';
    }

    public function scopeGeneral($query)
    {
        return $query->where('type', 1);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
