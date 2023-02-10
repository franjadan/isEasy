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
        $stores = Store::orderBy('name')->get();

        $data = [
			'stores' => StoreResource::collection($stores),
		];

		return response()->json(["response" => ["code" => 1, "data" => $data]], 200);

    }
}
