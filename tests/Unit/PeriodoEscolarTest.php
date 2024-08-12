<?php

namespace Tests\Unit;

use App\Http\Controllers\periodoEscolarController;
use App\Models\Administrador;
use App\Models\Departamento;
use App\Models\Docente;
use App\Models\document;
use App\Models\expediente;
use App\Models\PeriodoEscolar;
use App\Models\Personal;
use App\Models\Secretaria;
use App\Models\TipoDocumento;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Inertia\Response as InertiaResponse;

class PeriodoEscolarTest extends TestCase
{
    use RefreshDatabase;
    public function test_periodo_escolar_tiene_muchos_documentos(){
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
        $this->assertInstanceOf(Collection::class, $periodoEscolar->documento);
        $this->assertInstanceOf(document::class, $periodoEscolar->documento->first());
    }

    public function test_renderiza_correctamente_la_vista_de_departamentos_secretaria(){
        $departamento = Departamento::create([
            'IdDepartamento' => 1234,
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'IdPersonal' => 777,
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo',
        ]);
        $secretaria = Secretaria::create([
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $user = User::create([
            'id' => 7777,
            'email' => 'test@itoaxaca.edu.mx',
            'password' => null,
            'IdPersonal' => $personal->IdPersonal,
        ]);
        Auth::login($user);

        DB::table('periodo_escolar')->insert([
            ['IdPeriodoEscolar' => 1, 'nombre_corto' => '2023-2024', 'fechaInicio' => '2023-01-01', 'fechaTermino' => '2024-01-01'],
            ['IdPeriodoEscolar' => 2, 'nombre_corto' => '2022-2023', 'fechaInicio' => '2022-01-01', 'fechaTermino' => '2023-01-01'],
        ]);
        
        $controller = new periodoEscolarController();

        // Simular una solicitud
        $request = Request::create('/periodoEscolar', 'GET');
        $response = $controller->index($request);

        // Verificar los datos devueltos
        $this->assertInstanceOf(InertiaResponse::class, $response);
        // Verificar contenido de la respuesta
        $responseContent = $response->toResponse($request)->getContent();
        $this->assertStringContainsString('Dashboard_secre_PeriodoEscolar', $responseContent);   
    }

    public function test_renderiza_correctamente_la_vista_de_departamentos_administrador(){
        $departamento = Departamento::create([
            'IdDepartamento' => 1234,
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'IdPersonal' => 777,
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo',
        ]);
        $adminstrador = Administrador::create([
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $user = User::create([
            'id' => 7777,
            'email' => 'test@itoaxaca.edu.mx',
            'password' => null,
            'IdPersonal' => $personal->IdPersonal,
        ]);
        Auth::login($user);

        DB::table('periodo_escolar')->insert([
            ['IdPeriodoEscolar' => 1, 'nombre_corto' => '2023-2024', 'fechaInicio' => '2023-01-01', 'fechaTermino' => '2024-01-01'],
            ['IdPeriodoEscolar' => 2, 'nombre_corto' => '2022-2023', 'fechaInicio' => '2022-01-01', 'fechaTermino' => '2023-01-01'],
        ]);
        
        $controller = new periodoEscolarController();

        // Simular una solicitud
        $request = Request::create('/periodoEscolar', 'GET');
        $response = $controller->index($request);

        // Verificar los datos devueltos
        $this->assertInstanceOf(InertiaResponse::class, $response);
        // Verificar contenido de la respuesta
        $responseContent = $response->toResponse($request)->getContent();
        $this->assertStringContainsString('Dashboard_admin_PeriodoEscolar', $responseContent);   
    }

    public function test_Nuevo_periodoEscolar_exitoso()
    {
        // Desactiva eventos para evitar que realmente se dispare el evento Registered
        Event::fake();

        // Crear una solicitud simulada
        $request = Request::create('/periodoEscolar', 'PUT', [
            'fechaInicio' => '2024-01-01',
            'fechaTermino' => '2024-06-03',
            'nombre_corto' => 'ENE-JUN 2024',
        ]);

        $controller = new periodoEscolarController();

        $controller->nuevoPeriodoEscolar($request);

        $this->assertDatabaseHas('periodo_escolar', [
            'fechaInicio' => '2024-01-01',
            'fechaTermino' => '2024-06-03',
            'nombre_corto' => 'ENE-JUN 2024',
        ]);
    }

    public function test_Nuevo_periodo_escolar_falla_por_no_ser_unico(){
        PeriodoEscolar::create([
            'fechaInicio' => '2024-01-01',
            'fechaTermino' => '2024-06-03',
            'nombre_corto' => 'ENE-JUN 2024',
        ]);
        
        $this->expectExceptionMessage('El nombre corto ya ha sido tomado');
        $this->expectException(ValidationException::class);

        // Crear una solicitud simulada
         $request = Request::create('/periodoEscolar', 'PUT', [
            'fechaInicio' => '2024-01-01',
            'fechaTermino' => '2024-06-03',
            'nombre_corto' => 'ENE-JUN 2024',
        ]);

        $controller = new periodoEscolarController();

        $controller->nuevoPeriodoEscolar($request);
    }

    public function test_Nuevo_periodo_escolar_falla_por_fechas__de_inicio_mayor_que_la_fecha_de_termino(){
        $this->expectExceptionMessage('La fecha de inicio no puede ser despues de la fecha de termino');
        $this->expectException(ValidationException::class);
        // Crear una solicitud simulada
        $request = Request::create('/periodoEscolar', 'PUT', [
            'fechaInicio' => '2024-07-01',
            'fechaTermino' => '2024-06-03',
            'nombre_corto' => 'ENE-JUN 2024',
        ]);

        $controller = new periodoEscolarController();

        $controller->nuevoPeriodoEscolar($request);
    }

    public function test_Nuevo_periodo_escolar_falla_por_fechas_iguales(){
        $this->expectExceptionMessage('Las fechas no pueden ser iguales');
        $this->expectException(ValidationException::class);
        // Crear una solicitud simulada
        $request = Request::create('/periodoEscolar', 'PUT', [
            'fechaInicio' => '2024-07-01',
            'fechaTermino' => '2024-07-01',
            'nombre_corto' => 'ENE-JUN 2024',
        ]);

        $controller = new periodoEscolarController();

        $controller->nuevoPeriodoEscolar($request);
    }

    public function test_editar_PeriodoEscolar(){
        $periodoEscolar = PeriodoEscolar::create([
            'fechaInicio' => '2024-07-01',
            'fechaTermino' => '2024-07-01',
            'nombre_corto' => 'ENE-JUN 2024',
        ]);

        $request = Request::create(route('periodoEscolar.editar'), 'PUT', [
            'IdPeriodoEscolar' => $periodoEscolar->IdPeriodoEscolar,
            'fechaInicio' => '2023-01-01',
            'fechaTermino' => '2023-07-01',
            'nombre_corto' => 'ENE-JUN 2023 2',
        ]);

        $controller = new periodoEscolarController();

        $controller->editarPeriodoEscolar($request);

        $this->assertDatabaseHas('periodo_escolar', [
            'IdPeriodoEscolar' => $periodoEscolar->IdPeriodoEscolar,
            'fechaInicio' => '2023-01-01',
            'fechaTermino' => '2023-07-01',
            'nombre_corto' => 'ENE-JUN 2023 2',
        ]);
    }

    public function test_borrar_periodoEscolar(){
        $periodoEscolar = PeriodoEscolar::create([
            'fechaInicio' => '2024-07-01',
            'fechaTermino' => '2024-07-01',
            'nombre_corto' => 'ENE-JUN 2024',
        ]);

        $request = Request::create(route('periodoEscolar.borrar'), 'DELETE', [
            'IdPeriodoEscolar' => $periodoEscolar->IdPeriodoEscolar,
        ]);

        $controller = new periodoEscolarController();

        $controller->borrarPeriodoEscolar($request);

        $this->assertDatabaseMissing('periodo_escolar',[
            'IdPeriodoEscolar' => $periodoEscolar->IdPeriodoEscolar,
            'fechaInicio' => '2024-07-01',
            'fechaTermino' => '2024-07-01',
            'nombre_corto' => 'ENE-JUN 2024',
        ]);
    }
}
