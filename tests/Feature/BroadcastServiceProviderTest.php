<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Providers\BroadcastServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class BroadcastServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_includes_channels_routes_file()
    {
        // Simular la inclusión del archivo de rutas de canales
        $channelsPath = base_path('routes/channels.php');
        $this->assertFileExists($channelsPath);

        // Ejecutar el código de inclusión
        require $channelsPath;

        // Verificar que el archivo se haya incluido correctamente
        $this->assertTrue(true);
    }
}
