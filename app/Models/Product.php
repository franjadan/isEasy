<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function scopeSearch($query){
        $query->when(request('name'), function($query, $search) {
            return $query->where('name', 'like', "%{$search}%");
        });
    }
}
