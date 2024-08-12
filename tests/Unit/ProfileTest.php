<?php

namespace Tests\Unit;

use App\Models\Administrador;
use App\Models\Departamento;
use App\Models\Docente;
use App\Models\Personal;
use App\Models\Secretaria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;
    public function test_vista_perfil_secretaria_se_renderiza_correctamente()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $departamento2 = Departamento::create([
            'nombreDepartamento' => 'Civil',
        ]);
        $departamento3 = Departamento::create([
            'nombreDepartamento' => 'Quimica',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $user = User::create([
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $secretaria = Secretaria::create([
            'IdPersonal' => $personal->IdPersonal,
        ]);
        Auth::login($user);

        $response = $this->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Profile/Edit_secretaria')
            ->has('user')
            ->has('personal')
            ->has('departamentos'));
    }

    public function test_vista_perfil_administrador_se_renderiza_correctamente()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $departamento2 = Departamento::create([
            'nombreDepartamento' => 'Civil',
        ]);
        $departamento3 = Departamento::create([
            'nombreDepartamento' => 'Quimica',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $user = User::create([
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $administrador = Administrador::create([
            'IdPersonal' => $personal->IdPersonal,
        ]); 

        Auth::login($user);

        $response = $this->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Profile/Edit_admin')
            ->has('user')
            ->has('personal')
            ->has('departamentos'));
    }

    public function test_vista_perfil_docente_se_renderiza_correctamente()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $departamento2 = Departamento::create([
            'nombreDepartamento' => 'Civil',
        ]);
        $departamento3 = Departamento::create([
            'nombreDepartamento' => 'Quimica',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $user = User::create([
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $docente = Docente::create([
            'IdPersonal' => $personal->IdPersonal,
            'GradoAcademico' => 'Licenciatura',
        ]);

        Auth::login($user);

        $response = $this->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Profile/Edit')
            ->has('user')
            ->has('personal')
            ->has('departamentos'));
    }

    public function test_actualizacion_de_datos_del_perfil_exitosa()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $departamento2 = Departamento::create([
            'nombreDepartamento' => 'Civil',
        ]);
        $departamento3 = Departamento::create([
            'nombreDepartamento' => 'Quimica',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $user = User::create([
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $docente = Docente::create([
            'IdPersonal' => $personal->IdPersonal,
            'GradoAcademico' => 'Licenciatura',
        ]);

        Auth::login($user);

        $response = $this->patch(route('profile.update'), [
            'name' => 'Juana',
            'lastname' => 'Perez',
            'Departamento' => ['IdDepartamento' => $departamento2->IdDepartamento],
            'Sexo' => 'Mujer',
            'email' => 'juana.perez@oaxaca.tecnm.mx',
        ]);

        $response->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('users', ['email' => 'juana.perez@oaxaca.tecnm.mx']);
        $this->assertDatabaseHas('personal', ['Nombre' => 'Juana', 'Apellidos' => 'Perez', 'Sexo' => 'Mujer']);
    }

    public function test_actualizacion_de_datos_de_perfil_con_error_por_correo_incorrrecto()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->withoutExceptionHandling(); 
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $departamento2 = Departamento::create([
            'nombreDepartamento' => 'Civil',
        ]);
        $departamento3 = Departamento::create([
            'nombreDepartamento' => 'Quimica',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $user = User::create([
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $docente = Docente::create([
            'IdPersonal' => $personal->IdPersonal,
            'GradoAcademico' => 'Licenciatura',
        ]);

        Auth::login($user);

        $this->expectExceptionMessage('El dominio debe ser de la institución (@itoaxaca.edu.mx o @oaxaca.tecnm.mx)');
        $this->expectException(ValidationException::class);

        $this->patch(route('profile.update'), [
            'name' => 'Tadeo',
            'lastname' => 'Cruz',
            'Departamento' => ['IdDepartamento' => $departamento3->IdDepartamento],
            'Sexo' => 'Hombre',
            'email' => 'taddeocr7@gmail.com',
        ]);
    }
}
