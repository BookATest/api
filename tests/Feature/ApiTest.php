<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiTest extends TestCase
{
    public function test_v1_contains_correct_information()
    {
        $response = $this->get('/v1');

        $response->assertStatus(200);
        $response->assertJson([
            'version' => 'v1.0',
            'base_path' => url('/v1'),
        ]);
    }
}
