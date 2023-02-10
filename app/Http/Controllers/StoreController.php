<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use App\Http\Resources\StoreResource;

class StoreController extends Controller
{
    /**
     * Muestra un listado de tiendas relacionadas con sus productos 
     *
     * @return json
     */
    public function index(){
        $stores = Store::search()->orderBy('name')->get();

        $data = [
			'stores' => StoreResource::collection($stores),
		];

		return response()->json(["response" => ["code" => 1, "data" => $data]], 200);

    }


    /**
     * Muestra el detalle de una tienda específica relacionada con sus productos
     *
     * @return json
     */
    public function show(Store $store){

        $data = [
            'store' => new StoreResource($store)
        ];

		return response()->json(["response" => ["code" => 1, "data" => $data]], 200);
    }
}
