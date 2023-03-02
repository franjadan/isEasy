<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Store, Product, StoreProduct};
use App\Http\Resources\StoreResource;
use App\Http\Requests\Api\StoreRequest;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    /**
     * Muestra un listado de tiendas relacionadas con sus productos 
     *
     * @return json
     */
    public function index(){
        $stores = Store::search()->orderBy('name')->get();

		return response()->json(["data" => StoreResource::collection($stores)], 200);

    }


    /**
     * Muestra el detalle de una tienda específica relacionada con sus productos
     *
     * @return json
     */
    public function show(Store $store){

		return response()->json(["data" => new StoreResource($store)], 200);
    }

    /**
     * Creación de la tienda
     *
     * @param StoreRequest $request
     * @return json
     */
    public function store(StoreRequest $request){

        $request->createStore();

        return response()->json([], 201);
    }

    /**
     * Edición de la tienda
     *
     * @param StoreRequest $request
     * @param Store $store
     * @return json
     */
    public function update(StoreRequest $request, Store $store){

        $request->updateStore($store);

        return response()->json([], 200);
    }

    /**
     * Función para eliminar una tienda
     *
     * @param Store $store
     * @return json
     */
    public function destroy(Store $store){

        $store->delete();

        return response()->json([], 200);
    }

    /**
     * Función que simula una venta de un producto en una determinada tienda
     * Recibe como parámetro la cantidad a vender
     *
     * @param Request $request
     * @param Store $store
     * @param Product $product
     * @return json
     */
    public function sell(Request $request, Store $store, Product $product){

        $quantity = $request->get('quantity');

        if($quantity != null){
            $storeProduct = StoreProduct::where('store_id', $store->id)->where('product_id', $product->id)->first();

            if($storeProduct != null){

                if($storeProduct->quantity >= $quantity){
                    $storeProduct->quantity -= $quantity;
                    $storeProduct->save();

                    if($storeProduct->quantity == 0){
                        return response()->json(['data' => ['message' => 'Te has quedado sin stock del artículo.']], 200);
                    }elseif($storeProduct->quantity < 5){
                        return response()->json(['data' => ['message' => 'Quedan pocos artículos en stock.']], 200);
                    }else{
                        return response()->json(['data' => []], 200);
                    }
                }else{
                    return response()->json(["data" => ["message" => 'No tienes stock suficiente para realizar la operación.']], 400);
                } 
            }else{
                return response()->json(["data" => ["message" => 'No se ha encontrado el producto asociado a la tienda.']], 400);
            }
        }else{
            return response()->json(["data" => ["message" => 'No has introducido la cantidad a vender.']], 400);
        }
        
        
    }
}
