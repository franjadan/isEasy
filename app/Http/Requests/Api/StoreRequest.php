<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use App\Models\{Store, Product, StoreProduct};
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => ['required', 'min:3', Rule::unique('stores', 'name')->ignore($this->store)],
            'products.*.id' => ['sometimes', Rule::exists('products', 'id')],
            'products.*.name' => ['required', 'min:3', 
            Rule::unique('products', 'name')->where(function ($query) {
                if(!(count(request()->input('products.*.id')) && request()->input('products.*.id')[0] == null)){
                    $query->whereNotIn('id', request()->input('products.*.id'));
                }
                return $query;
            })],
            'products.*.quantity' => ['required', 'numeric']
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(){
        return [
            'name' => 'nombre',
            'products.*.id' => 'id del producto',
            'products.*.name' => 'nombre del producto',
            'products.*.quantity' => 'cantidad del producto',
        ];
    }

    /**
     * @overrride
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['response' => ['code' => -1, 'error_data' => $validator->errors()]], 200));
    }


    /**
     * Función para crear una nueva tienda
     * 
     * Parámetros:
     *  name
     *  products[*][name]
     *  products[*][quantity]: Cantidad de producto asociado a la tienda
     *
     * @return void
     */
    public function createStore(){
        DB::transaction(function (){

            $store = Store::create([
                'name' => $this->name
            ]);

            foreach($this->products as $product){

                $newProduct = Product::create([
                    'name' => $product['name'],
                ]);

                $storeProduct = StoreProduct::create([
                    'store_id' => $store->id,
                    'product_id' => $newProduct->id,
                    'quantity' => $product['quantity']
                ]);
            }
        });
    }
    

    /**
     * Función para actualizar tienda
     * 
     * Si se encuentra el parámetro id dentro del array de parámetros editará el producto
     * Si no lo encuentra lo crea nuevo
     * 
     * Parámetros:
     *  name
     *  products[*][id]: Opcional
     *  products[*][name]
     *  products[*][quantity]: Cantidad de producto asociado a la tienda
     *
     * @return void
     */
    public function updateStore(Store $store){
        DB::transaction(function () use ($store){

            $store->update([
                'name' => $this->name
            ]);

            foreach($this->products as $product){

                if(!in_array('id', array_keys($product))){

                    $productObj = Product::create([
                        'name' => $product['name'],
                    ]);

                }else{
                    $productObj = Product::find($product['id']);

                    $productObj->update([
                        'name' => $product['name'],
                    ]);
                }

                $storeProduct = StoreProduct::create([
                    'store_id' => $store->id,
                    'product_id' => $productObj->id,
                    'quantity' => $product['quantity']
                ]);
            }
        });
    }
}
