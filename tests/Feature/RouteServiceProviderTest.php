<?php

namespace Tests\Feature;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_registers_api_routes()
    {
        $this->artisan('route:clear'); // Limpiar rutas cargadas previamente

        // Crear instancia del proveedor y llamar al método boot
        $serviceProvider = new \App\Providers\RouteServiceProvider($this->app);
        $serviceProvider->boot();

        // Obtener todas las rutas y buscar rutas API
        $routes = Route::getRoutes();
        $apiRoutes = array_filter($routes->getRoutesByMethod()['GET'], function ($route) {
            return strpos($route->uri(), 'api') === 0;
        });

        $this->assertNotEmpty($apiRoutes, 'API routes are not registered.');
    }

    /** @test */
    public function it_registers_web_routes()
    {
        $this->artisan('route:clear'); // Limpiar rutas cargadas previamente

        // Crear instancia del proveedor y llamar al método boot
        $serviceProvider = new \App\Providers\RouteServiceProvider($this->app);
        $serviceProvider->boot();

        // Obtener todas las rutas y verificar algunas rutas web
        $routes = Route::getRoutes();
        $webRoutes = array_filter($routes->getRoutesByMethod()['GET'], function ($route) {
            return strpos($route->uri(), '/') === 0 && !strpos($route->uri(), 'api/');
        });

        $this->assertNotEmpty($webRoutes, 'Web routes are not registered.');
        $this->assertTrue(
            !empty(array_filter($webRoutes, function ($route) {
                return in_array('web', $route->action['middleware']);
            })),
            'Web routes do not have the correct middleware.'
        );
    }
}
