<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\PasswordController;
use App\Models\Administrador;
use App\Models\Departamento;
use App\Models\Personal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_actualizar_contraseña(){
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => null,
            'Sexo' => null,
        ]);
        $administrador = Administrador::create([
            'IdPersonal' => $personal->IdPersonal,
        ]);

        $user = User::create([
            'IdPersonal' => $personal->IdPersonal,
            'password' => Hash::make('passwordD@7'),
            'email' => 'roberto.m@itoaxaca.edu.mx'
        ]);

        $this->actingAs($user);

        $request = Request::create(route('password.update'), 'PUT' ,[
            'current_password' => 'passwordD@7',
            'password' => 'new-passworD@7',
            'password_confirmation' => 'new-passworD@7',
        ]);

        $controller = new PasswordController();
        $controller->update($request);
        $user = $user->fresh();
        $this->assertTrue(Hash::check('new-passworD@7', $user->password));
    }

    public function test_ingresar_datos_por_primera_vez_en_el_sistema()
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
        $administrador = Administrador::create([
            'IdPersonal' => $personal->IdPersonal,
        ]);

        $user = User::create([
            'IdPersonal' => $personal->IdPersonal,
            'password' => null,
            'email' => 'roberto.m@itoaxaca.edu.mx'
        ]);

        $this->actingAs($user);

        $request = Request::create(route('password.first'), 'PUT' ,[
            
            'password' => 'new-passworD@7',
            'password_confirmation' => 'new-passworD@7',
            'Departamento' => ['IdDepartamento' => $departamento->IdDepartamento],
            'Sexo' => 'Hombre',
        ]);

        $controller = new PasswordController();
        $controller->firstPassword($request);
        $user = $user->fresh();
        $this->assertTrue(Hash::check('new-passworD@7', $user->password));

        $this->assertDatabaseHas('personal', [
            'IdPersonal' => $personal->IdPersonal,
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
    }
}
