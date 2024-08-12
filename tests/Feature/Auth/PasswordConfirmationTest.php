<?php

namespace Tests\Feature\Auth;

use App\Models\Departamento;
use App\Models\Personal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_vista_de_confirmacion_de_contraseña_se_renderiza_correctamente(): void
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

        $response = $this->actingAs($user)->get('/confirm-password');

        $response->assertStatus(200);
    }

    public function test_la_confirmacion_de_contraseña_se_hace_correctamente(): void
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

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'passworD@7',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    public function test_la_confirmacion_de_contraseña_es_invalida_por_contraseña_incorrecta(): void
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

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
    }
    /* public function test_primeros_datos_al_iniciar_por_primera_vez()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => null,
            'Sexo' => null,
        ]);
        $user = User::create([
            'email' => 'test@itoaxaca.edu.mx',
            'password' => null,
            'IdPersonal' => $personal->IdPersonal,
            'email_verified_at' => now(),
        ]);

        // Datos de la solicitud
        $data = [
            'password' => 'passwordD@7',
            'password_confirmation' => 'passwordD@7',
            'Departamento' => ['IdDepartamento' => $departamento->IdDepartamento],
            'Sexo' => 'Masculino',
        ];

        // Autenticar al usuario
        $this->actingAs($user);

        // Enviar la solicitud POST
        $response = $this->post(route('password.first'), $data);

        // Refrescar el usuario y el personal desde la base de datos
        $user->refresh();
        $personal->refresh();

        // Verificar que los datos se hayan actualizado en la base de datos
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'password' => Hash::check('passwordD@7', $user->password),
        ]);

        $this->assertDatabaseHas('personals', [
            'IdPersonal' => $personal->IdPersonal,
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Masculino',
        ]);
    } */
}
