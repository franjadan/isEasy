<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    /**
     * Filtro de búsqueda
     *
     * @param string $query
     * @return Collection
     */
    public function scopeSearch($query){
        $query->when(request('name'), function($query, $search) {
            return $query->where('stores.name', 'like', "%{$search}%");
        });

        $query->when(request('product'), function($query, $search) {
            return $query->whereHas('products', function ($q) use ($search) {
                $q->where('products.id', $search);
            });
        });

        $query->when(request('product_name'), function($query, $search) {
            return $query->whereHas('products', function ($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%");
            });
        });
    }

    public function store_products(){
        return $this->hasMany(StoreProduct::class);
    }

    public function products(){
        return $this->belongsToMany(Product::class, 'stores_products');
    }
}
