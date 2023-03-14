<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RoutesTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_wrongNodes()
    {
        //Nodos incorrectos
        $response = $this->postJson('/api/nodes/findRoute', ['origin' => 999, 'destination' => 999]);
        $response->assertStatus(200)
                ->assertJson([
                    "status" => 400,
                    "data" => ["The selected origin is invalid.",
                               "The selected destination is invalid."
                              ],
                    "message" => 'Fallos: '
                ]);
    }
    public function test_equalNodes(){
        //Origen igual que destino
        $response = $this->postJson('/api/nodes/findRoute', ['origin' => 1, 'destination' => 1]);
        $response->assertStatus(200)
                ->assertJson([
                    "status" => 400,
                    "data" => 'The origin can not be destination',
                    "message" => 'Something was wrong'
                ]);
    }
}
