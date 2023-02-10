<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreProduct extends Model
{
    use HasFactory;

    protected $table = 'stores_products';

    protected $fillable = [
        'store_id', 'product_id', 'quantity'
    ];

    public function store(){
        return $this->belongsTo(Store::class);
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }
}
