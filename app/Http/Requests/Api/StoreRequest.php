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
            'products.*.name' => ['required', 'min:3'],
            'products.*.quantity' => ['required', 'numeric']
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator $validator
     *
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if($this->store != null){
                foreach($this->products as $key => $product){

                    if(in_array('id', array_keys($product))){

                        $storeProduct = StoreProduct::where('store_id', $this->store->id)->where('product_id', $product['id'])->first();

                        if($storeProduct == null){
                            $validator->errors()->add('products.' . $key . '.id', 'No existe este producto asociado a la tienda.');
                            return;
                        }
                        
                        $searchProduct = Product::where('id', '<>', $product['id'])->where('name', $product['name'])->first();
                    }else{
                        $searchProduct = Product::where('name', $product['name'])->first();
                    }

                    if($searchProduct != null){
                        $validator->errors()->add('products.' . $key . '.name', 'Ese nombre está en uso.');
                    }
                }
            }
        });
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

                    $storeProduct = StoreProduct::create([
                        'store_id' => $store->id,
                        'product_id' => $productObj->id,
                        'quantity' => $product['quantity']
                    ]);

                }else{
                    $productObj = Product::find($product['id']);

                    if($productObj != null){

                        $productObj->update([
                            'name' => $product['name'],
                        ]);
    
                        $storeProduct = StoreProduct::where('store_id', $store->id)->where('product_id', $productObj->id)->first();
    
                        if($storeProduct != null){
                            $storeProduct->update([
                                'quantity' => $product['quantity']
                            ]);
                        }
                    }
                }

                
            }
        });
    }
}
