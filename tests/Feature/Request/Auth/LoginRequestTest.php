<?php

namespace Tests\Feature\Request\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Departamento;
use App\Models\Personal;
use App\Models\Secretaria;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Event;

class LoginRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Configuración adicional si es necesario
    }

    public function testAuthorize()
    {
        $request = new LoginRequest();
        $this->assertTrue($request->authorize());
    }

    public function testRules()
    {
        $request = new LoginRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
        $this->assertContains('required', $rules['email']);
        $this->assertContains('email', $rules['email']);
        $this->assertContains('required', $rules['password']);
        $this->assertContains('string', $rules['password']);
    }

    public function test_autenticacion_exitosa()
    {
        // Configurar el mock de autenticación para que devuelva true
        Auth::shouldReceive('attempt')
            ->once()
            ->with(['email' => 'test@example.com', 'password' => 'password'], false)
            ->andReturn(true);

        // Crear la solicitud
        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => 'false',
        ]);

        // Ejecutar el método authenticate
        $request->authenticate();

        // Verificar que no se haya lanzado ninguna excepción
        $this->assertTrue(true);
    }

    public function test_autenticacion_falla()
    {
        // Configurar el mock para ensureIsNotRateLimited
        RateLimiter::shouldReceive('tooManyAttempts')
            ->once()
            ->andReturn(false);

        // Configurar el mock de autenticación para que devuelva false
        Auth::shouldReceive('attempt')
            ->once()
            ->andReturn(false);

        // Configurar RateLimiter para que registre el intento fallido
        RateLimiter::shouldReceive('hit')
            ->once();

        // Crear la solicitud
        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        // Verificar que se lance una excepción de validación
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(trans('auth.failed'));

        // Ejecutar el método authenticate
        $request->authenticate();
    }

    public function test_asegurarse_que_no_este_limitado()
    {
        // Configurar RateLimiter para que devuelva false (no está limitado)
        RateLimiter::shouldReceive('tooManyAttempts')
            ->once()
            ->andReturn(false);

        // Crear la solicitud
        $request = new LoginRequest();

        // Ejecutar el método ensureIsNotRateLimited
        $request->ensureIsNotRateLimited();

        // Verificar que no se haya lanzado ninguna excepción
        $this->assertTrue(true);
    }

    public function test_asegurarse_que_si_esta_limitado()
    {
        // Configurar RateLimiter para que devuelva true (está limitado)
        RateLimiter::shouldReceive('tooManyAttempts')
            ->once()
            ->andReturn(true);

        // Configurar el número de segundos restantes
        RateLimiter::shouldReceive('availableIn')
            ->once()
            ->andReturn(60);

        // Capturar el evento de bloqueo
        Event::fake();

        // Crear la solicitud
        $request = new LoginRequest();

        // Verificar que se lance una excepción de validación
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(trans('auth.throttle', ['seconds' => 60, 'minutes' => 1]));

        // Ejecutar el método ensureIsNotRateLimited
        $request->ensureIsNotRateLimited();

        // Verificar que el evento Lockout se haya disparado
        Event::assertDispatched(Lockout::class);
    }

    public function test_throttle_key()
    {
        // Crear la solicitud con un email y dirección IP específicos
        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
        ]);

        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        // Obtener la clave de limitación de tasa
        $throttleKey = $request->throttleKey();

        // Verificar que la clave sea correcta
        $expectedKey = Str::transliterate(Str::lower('test@example.com') . '|' . '127.0.0.1');
        $this->assertEquals($expectedKey, $throttleKey);
    }
}
