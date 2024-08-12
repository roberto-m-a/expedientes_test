<?php

namespace Tests\Feature\Auth;

use App\Models\Departamento;
use App\Models\Personal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_contraseña_puede_ser_actualizada(): void
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $user = User::create([
            'email' => 'test@itoaxaca.edu.mx',
            'password' => Hash::make('passworD@7'),
            'IdPersonal' => $personal->IdPersonal,
            'email_verified_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'passworD@7',
                'password' => 'new-passwordD@7',
                'password_confirmation' => 'new-passwordD@7',
            ]);

        $response
            ->assertSessionHasNoErrors();
    }

    public function test_la_contraseña_antigua_debe_ser_ingresada_para_poder_actualizar_la_contraseña(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $user = User::create([
            'email' => 'test@itoaxaca.edu.mx',
            'password' => Hash::make('passworD@7'),
            'IdPersonal' => $personal->IdPersonal,
            'email_verified_at' => now(),
        ]);

        // Autenticar como el usuario creado y hacer la solicitud para actualizar la contraseña
        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'wrong-passwordD@7',
                'password' => 'new-passwordD@7',
                'password_confirmation' => 'new-passwordD@7',
            ]);
        // Depuración: Imprimir el contenido de la sesión
        $response->assertRedirect('/profile');
        /* $errors = session('errors');
        dd($errors ? $errors->getBag('default')->get('current_password') : 'No errors'); */

        // Verificar que la sesión tiene errores para el campo 'current_password'
        $response->assertSessionHasErrors('current_password');
    }
}
