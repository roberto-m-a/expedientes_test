<?php

namespace Tests\Feature\Auth;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_vista_de_registro_se_renderiza_correctamente(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

}
