<?php

namespace Tests\Unit;

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Models\Departamento;
use App\Models\Personal;
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Notifications\notificacionRegistroCorreo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use App\Models\Docente;
use App\Models\document;
use App\Models\expediente;
use App\Models\PeriodoEscolar;
use App\Models\TipoDocumento;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Route;
use Inertia\Response as InertiaResponse;
use Illuminate\Support\Facades\Session;


class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_tiene_relacion_con_un_personal(){
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
            'id' => 7777,
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $this->assertInstanceOf(Personal::class, $user->personal);
        $this->assertEquals($user->IdPersonal, $personal->IdPersonal);
    }
    public function test_el_usuario_tiene_relacion_con_muchos_documentos(){
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
            'id' => 7777,
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $docente = Docente::create([
            'IdPersonal' => $personal->IdPersonal,
            'GradoAcademico' => 'Licenciatura',
        ]);
        
        $expediente = expediente::create([
            'IdDocente' => $docente->IdDocente,
        ]);
        $periodoEscolar = PeriodoEscolar::create([
            'fechaInicio' => '2024-01-01',
            'fechaTermino' => '2024-06-03',
            'nombre_corto' => 'ENE-JUN 2024',
        ]);
        $tipoDoc = TipoDocumento::create([
            'nombreTipoDoc' => 'Nuevo tipo documento',
        ]);

        $document = document::create([
            'Titulo' => 'Document Title',
            'fechaExpedicion' => '2024-08-06',
            'fechaEntrega' => '2024-08-07',
            'Estatus' => 'Activo',
            'region' => 'Region 1',
            'IdTipoDocumento' => $tipoDoc->IdTipoDocumento,
            'IdPeriodoEscolar' => $periodoEscolar->IdPeriodoEscolar,
            'IdExpediente' => $expediente->IdExpediente,
            'IdDepartamento' => $departamento->IdDepartamento,
            'IdUsuario' => $user->id,
            'URL' => 'http://example.com',
            'Dependencia' => 'Dependencia 1',
        ]);
        //Verifica que las relaciones regresen colecciones de documentos
        $this->assertInstanceOf(Collection::class, $user->documento);
        $this->assertInstanceOf(document::class, $user->documento->first());
    }
    public function test_crear_usuario_docente_desde_la_vista_de_registro()
    {
        // Fake events and notifications
        Event::fake();
        Notification::fake();
        $request = Request::create('/aniadir-usuario', 'POST', [
            'name' => 'Roberto',
            'lastname' => 'Manuel',
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'email_confirmation' => 'roberto.manuel@oaxaca.tecnm.mx',
        ]);

        $controller = new RegisteredUserController();

        $controller->store($request);

        // Check if the personal record is created
        $this->assertDatabaseHas('personal', [
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
        ]);

        $personal = Personal::where('Nombre', 'Roberto')->first();

        // Check if the user record is created
        $this->assertDatabaseHas('users', [
            'email' => 'roberto.manuel@oaxaca.tecnm.mx',
            'IdPersonal' => $personal->IdPersonal,
        ]);

        $user = User::where('email', 'roberto.manuel@oaxaca.tecnm.mx')->first();

        // Check if the docente record is created
        $this->assertDatabaseHas('docente', [
            'GradoAcademico' => 'Licenciatura',
            'IdPersonal' => $personal->IdPersonal,
        ]);

        $docente = Docente::where('IdPersonal', $personal->IdPersonal)->first();

        // Check if the expediente record is created
        $this->assertDatabaseHas('expediente', [
            'IdDocente' => $docente->IdDocente,
        ]);

        $expediente = Expediente::where('IdDocente', $docente->IdDocente)->first();

        // Check if the user is authenticated
        $this->assertTrue(Auth::check());
    }

    /** @test */
    public function test_Validar_un_correo_correcto()
    {
        $request = Request::create('/validar-usuario', 'POST', [
            'email' => 'test@itoaxaca.edu.mx',
            'email_confirmation' => 'test@itoaxaca.edu.mx',
        ]);

        $controller = new RegisteredUserController();

        $this->expectNotToPerformAssertions();

        $controller->validarUsuario($request);
    }

    /** @test */
    public function test_Validar_los_correos_no_coinciden()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Los correos no coinciden');

        $request = Request::create('/validar-usuario', 'POST', [
            'email' => 'test@itoaxaca.edu.mx',
            'email_confirmation' => 'different@itoaxaca.edu.mx',
        ]);

        $controller = new RegisteredUserController();
        $controller->validarUsuario($request);
    }

    /** @test */
    public function test_Validar_el_dominio_institucional_en_el_correo()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('El dominio debe ser de la institución (@itoaxaca.edu.mx o @oaxaca.tecnm.mx)');

        $request = Request::create('/validar-usuario', 'POST', [
            'email' => 'test@gmail.com',
            'email_confirmation' => 'test@gmail.com',
        ]);

        $controller = new RegisteredUserController();
        $controller->validarUsuario($request);
    }

    /** @test */
    public function test_Validar_correo_no_unico()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo',
        ]);
        User::create([
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personal->IdPersonal,
        ]);

        $this->expectException(ValidationException::class);

        $request = Request::create('/validar-usuario', 'POST', [
            'email' => 'test@itoaxaca.edu.mx',
            'email_confirmation' => 'test@itoaxaca.edu.mx',
        ]);

        $controller = new RegisteredUserController();
        $controller->validarUsuario($request);
    }

    /** @test */
    public function test_usuario_validado_y_creado_con_envio_de_notificacion()
    {
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo',
        ]);
        // Finge el envío de notificaciones
        Notification::fake();

        // Datos del request
        $request = Request::create('/aniadir-usuario', 'POST', [
            'email' => 'test@itoaxaca.edu.mx',
            'IdPersonal' => $personal->IdPersonal,
        ]);

        $controller = new RegisteredUserController();

        // Ejecuta el método del controlador
        $controller->aniadirUsuario($request);

        // Verifica que el usuario se haya creado en la base de datos
        $this->assertDatabaseHas('users', [
            'email' => 'test@itoaxaca.edu.mx',
            'IdPersonal' => $personal->IdPersonal,
        ]);

        // Obtén el usuario creado
        $user = User::where('email', 'test@itoaxaca.edu.mx')->first();

        // Verifica que se haya enviado la notificación al usuario
        Notification::assertSentTo($user, notificacionRegistroCorreo::class);
    }
    /** @test */
    public function test_renderiza_correctamente_la_vista_para_el_inicio_de_sesion()
    {
        // Crear una instancia del controlador
        $controller = new RegisteredUserController();

        // Simula una ruta para 'password.request'
        Route::shouldReceive('has')
            ->with('password.request')
            ->andReturn(true);

        // Simula un valor en la sesión
        Session::shouldReceive('get')
            ->with('status')
            ->andReturn('Password reset link sent.');

        // Ejecuta el método create
        $response = $controller->create();

        // Verifica que el método `create` devuelve una instancia de `InertiaResponse`
        $this->assertInstanceOf(InertiaResponse::class, $response);
    }
}
