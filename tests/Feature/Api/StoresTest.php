<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\{Store, Product, StoreProduct};
use App\Http\Resources\StoreResource;

class StoresTest extends TestCase
{
    use RefreshDatabase;

    /** @test */

    public function can_get_all_stores(){
        //Sin productos
        $store = Store::factory()->create();

        $response = $this->getJson(route('api.stores.index'));
        //$response = $this->getJson('/api/tiendas');

        //Pasa la prueba si recibe 200, pero no expresa la lógica real de lo que devuelve el JSON
        $response->assertOk();

        $response->assertJson([
            'data' => [
                [
                    'id' => $store->id,
                    'name' => $store->name,
                    'products' => [],
                ]
            ]
        ]);
    }

    /** @test */
    public function can_get_all_stores_with_products(){
        $store = Store::factory()->create();
        $product = Product::factory()->create();

        $storeProduct = StoreProduct::create([
            'store_id' => $store->id,
            'product_id' => $product->id,
            'quantity' => random_int(0,10),
        ]);

        $response = $this->getJson(route('api.stores.index'));
        //$response = $this->getJson('/api/tiendas');

        //Pasa la prueba si recibe 200, pero no expresa la lógica real de lo que devuelve el JSON
        $response->assertOk();

        $response->assertJson([
            'data' => [
                [
                    'id' => $store->id,
                    'name' => $store->name,
                    'products' => [
                        [
                            'id' => $product->id,
                            'name' => $product->name,
                            'quantity' => $storeProduct->quantity,
                        ]
                    ],
                ]
            ]
        ]);
    }

    /** @test */
    public function can_get_search_stores(){

        //Búsqueda por nombre de tienda 

        $store = Store::factory()->create([
            'name' => 'test'
        ]);

        $response = $this->getJson('/api/tiendas?name=test');

        //Pasa la prueba si recibe 200, pero no expresa la lógica real de lo que devuelve el JSON
        $response->assertOk();

        $response->assertJson([
            'data' => [
                [
                    'id' => $store->id,
                    'name' => $store->name,
                    'products' => [],
                ]
            ]
        ]);
    }

    /** @test */
    public function can_get_search_stores_with_products(){

        $store = Store::factory()->create([
            'name' => 'test'
        ]);

        $product = Product::factory()->create([
            'name' => 'test'
        ]);

        $storeProduct = StoreProduct::create([
            'store_id' => $store->id,
            'product_id' => $product->id,
            'quantity' => random_int(0,10),
        ]);

        //Búsqueda por producto

        $response = $this->getJson('/api/tiendas?product=' . $product->id);
        
        $response->assertOk();

        $response->assertJson([
            'data' => [
                [
                    'id' => $store->id,
                    'name' => $store->name,
                    'products' => [
                        [
                            'id' => $product->id,
                            'name' => $product->name,
                            'quantity' => $storeProduct->quantity,
                        ]
                    ],
                ]
            ]
        ]);

        //Búsqueda por nombre de producto
        $response = $this->getJson('/api/tiendas?product_name=' . $product->name);
    
        $response->assertOk();

        $response->assertJson([
            'data' => [
                [
                    'id' => $store->id,
                    'name' => $store->name,
                    'products' => [
                        [
                            'id' => $product->id,
                            'name' => $product->name,
                            'quantity' => $storeProduct->quantity,
                        ]
                    ],
                ]
            ]
        ]);
    }

    /** @test */
    public function can_get_store_detail(){

        $existingStore = Store::factory()->create();

        $response = $this->getJson('/api/tiendas/' . $existingStore->id);

        //Pasa la prueba si recibe 200, pero no expresa la lógica real de lo que devuelve el JSON
        $response->assertOk();

        $response->assertJson([
            'data' => [
                'id' => $existingStore->id,
                'name' => $existingStore->name,
                'products' => [],
            ]
        ]);
    }

    /** @test */
    public function can_get_store_detail_with_products(){

        $existingStore = Store::factory()->create();
        $existingProduct = Product::factory()->create();

        $storeProduct = StoreProduct::create([
            'store_id' => $existingStore->id,
            'product_id' => $existingProduct->id,
            'quantity' => random_int(0,10),
        ]);

        $response = $this->getJson('/api/tiendas/' . $existingStore->id);

        //Pasa la prueba si recibe 200, pero no expresa la lógica real de lo que devuelve el JSON
        $response->assertOk();

        $response->assertJson([
            'data' => [
                'id' => $existingStore->id,
                'name' => $existingStore->name,
                'products' => [
                    [
                        'id' => $existingProduct->id,
                        'name' => $existingProduct->name,
                        'quantity' => $storeProduct->quantity,
                    ]
                ],
            ]
        ]);
    }

    /** @test */
    public function can_store_a_store(){

        $newStore = Store::factory()->make([
            'name' => 'test'
        ]);

        $response = $this->postJson('/api/tiendas', $newStore->toArray());
        
        $response->assertCreated(); //201

        $this->assertDatabaseHas('stores', $newStore->toArray());
    }

    /** @test */
    public function can_store_a_store_with_products(){

        $newStore = Store::factory()->make([
            'name' => 'test'
        ]);
        $newProduct = Product::factory([
            'name' => 'test'
        ])->make();

        $data = $newStore->toArray();
        $data['products'][0] = [
            'name' => $newProduct->name,
            'quantity' => random_int(1,10),
        ];

        $secondNewProduct = Product::factory()->make([
            'name' => 'test2'
        ]);

        $data['products'][1] = [
            'name' => $secondNewProduct->name,
            'quantity' => random_int(1,10),
        ];
        
        $response = $this->postJson('/api/tiendas', $data);
        
        $response->assertCreated(); //201

        $this->assertDatabaseHas('stores', $newStore->toArray());
        $this->assertDatabaseHas('products', $newProduct->toArray());
        $this->assertDatabaseHas('products', $secondNewProduct->toArray());
    }

    /** @test */
    public function can_update_a_store(){

        $existingStore = Store::factory()->create([
            'name' => 'test'
        ]);
        
        $newStore = Store::factory()->make([
            'name' => 'test2'
        ]);

        $response = $this->putJson('/api/tiendas/' . $existingStore->id, $newStore->toArray());

        $response->assertOk();

        $this->assertDatabaseHas('stores', $newStore->toArray());
    }

     /** @test */
     public function can_update_a_store_with_products(){

        $existingStore = Store::factory()->create([
            'name' => 'testStore'
        ]);

        $existingProduct = Product::factory()->create([
            'name' => 'testProduct'
        ]);

        $storeProduct = StoreProduct::create([
            'store_id' => $existingStore->id,
            'product_id' => $existingProduct->id,
            'quantity' => random_int(0,10),
        ]);

        $newProduct = Product::factory()->make([
            'name' => 'testProduct2'
        ]);

        $newStore = Store::factory()->make([
            'name' => 'test',
        ]);

        $data = $newStore->toArray();

        $data['products'][0] = [
            'name' => $newProduct->name,
            'quantity' => random_int(1,10),
        ];

        $data['products'][1] = [
            'id' => $existingProduct->id,
            'name' => $existingProduct->name,
            'quantity' => random_int(1,10),
        ];

        $response = $this->putJson('/api/tiendas/' . $existingStore->id, $data);
                
        $response->assertOk(); //200

        $this->assertDatabaseHas('stores', $newStore->toArray());

        $this->assertDatabaseHas('products', [
            'id' => $existingProduct->id,
            'name' => $existingProduct->name, 
        ]);
        
        $this->assertDatabaseHas('products', $newProduct->toArray());
        
    }


    /** @test */
    public function can_delete_a_store(){

        $existingStore = Store::factory()->create();

        $response = $this->deleteJson('/api/tiendas/' . $existingStore->id);
    
        $response->assertOk();

        $this->assertDatabaseMissing('stores', $existingStore->toArray());
    }

    
    /** @test */
    public function can_sell_a_product(){

        $existingStore = Store::factory()->create([
            'name' => 'testStore'
        ]);

        $existingProduct = Product::factory()->create([
            'name' => 'testProduct'
        ]);

        $storeProduct = StoreProduct::create([
            'store_id' => $existingStore->id,
            'product_id' => $existingProduct->id,
            'quantity' => 5,
        ]);

        $response = $this->postJson('/api/tiendas/' . $existingStore->id . '/vender/' . $existingProduct->id, [
            'quantity' => 3
        ]);
    
        $response->assertOk();

        $response = $this->postJson('/api/tiendas/' . $existingStore->id . '/vender/' . $existingProduct->id, [
            'quantity' => 3
        ]);
    
        $response->assertStatus(400);

        $response = $this->postJson('/api/tiendas/' . $existingStore->id . '/vender/0'. $existingProduct->id, []);
    
        $response->assertStatus(400);

        $response = $this->postJson('/api/tiendas/' . $existingStore->id . '/vender/0', [
            'quantity' => 3
        ]);
    
        $response->assertStatus(404);

    }
}
