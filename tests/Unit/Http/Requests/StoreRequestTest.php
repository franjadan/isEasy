<?php

namespace Tests\Unit\Http\Requests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Store;

class StoreRequestTest extends TestCase
{  
    use RefreshDatabase;  

    private string $routePrefix = '/api/tiendas/';  

    /**
     * @test
     * @throws \Throwable
     */
    public function name_is_required(){

        $validatedField = 'name';
        $brokenRule = null;

        $store = Store::factory()->make([
            $validatedField => $brokenRule
        ]);

        $this->postJson($this->routePrefix, $store->toArray())
        ->assertStatus(422)
        ->assertJsonStructure(['data' => ['name']]);

        //Update

        $existingStore = Store::factory()->create();

        $newStore = Store::factory()->make([
            $validatedField => $brokenRule
        ]);

        $this->putJson($this->routePrefix . $existingStore->id, $newStore->toArray())
        ->assertStatus(422)
        ->assertJsonStructure(['data' => ['name']]);
    }

    /**
     * @test
     * @throws \Throwable
     */
    public function name_must_not_exceed_3_characters(){

        $validatedField = 'name';
        $brokenRule = 'ao';

        $store = Store::factory()->make([
            $validatedField => $brokenRule
        ]);

        $this->postJson($this->routePrefix, $store->toArray())
        ->assertStatus(422)
        ->assertJsonStructure(['data' => ['name']]);

        //Update

        $existingStore = Store::factory()->create();

        $newStore = Store::factory()->make([
            $validatedField => $brokenRule
        ]);

        $this->putJson($this->routePrefix . $existingStore->id, $newStore->toArray())
        ->assertStatus(422)
        ->assertJsonStructure(['data' => ['name']]);
    }

    /**
     * @test
     * @throws \Throwable
     */
    public function name_must_not_be_duplicated(){

        $validatedField = 'name';
        $brokenRule = 'test';

        $store = Store::factory()->make([
            $validatedField => $brokenRule
        ]);

        $this->postJson($this->routePrefix, $store->toArray())
        ->assertStatus(201);

        $duplicatedStore = Store::factory()->make([
            $validatedField => $brokenRule
        ]);

        $this->postJson($this->routePrefix, $store->toArray())
        ->assertStatus(422)
        ->assertJsonStructure(['data' => ['name']]);

        //Update

        $existingStore = Store::factory()->create();

        $newStore = Store::factory()->make([
            $validatedField => $brokenRule
        ]);

        $this->putJson($this->routePrefix . $existingStore->id, $newStore->toArray())
        ->assertStatus(422)
        ->assertJsonStructure(['data' => ['name']]);
    }
}
