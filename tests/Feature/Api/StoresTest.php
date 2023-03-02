<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Store;
use App\Http\Resources\StoreResource;

class StoresTest extends TestCase
{
    use RefreshDatabase;

    /** @test */

    public function can_get_all_stores(){

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
    public function can_store_a_store(){

        $newStore = Store::factory()->make();

        $response = $this->postJson('/api/tiendas', $newStore->toArray());
        
        $response->assertCreated(); //201

        $this->assertDatabaseHas('stores', $newStore->toArray());
    }

    /** @test */
    public function can_update_a_store(){

        $existingStore = Store::factory()->create();
        
        $newStore = Store::factory()->make();

        $response = $this->putJson('/api/tiendas/' . $existingStore->id, $newStore->toArray());

        $response->assertOk();

        $this->assertDatabaseHas('stores', $newStore->toArray());
    }

    /** @test */
    public function can_delete_a_store(){

        $existingStore = Store::factory()->create();

        $response = $this->deleteJson('/api/tiendas/' . $existingStore->id);
    
        $response->assertOk();

        $this->assertDatabaseMissing('stores', $existingStore->toArray());
    }
}
