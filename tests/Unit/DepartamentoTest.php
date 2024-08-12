<?php

namespace Tests\Unit;

use App\Http\Controllers\departamentoController;
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
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Inertia\Response as InertiaResponse;

class DepartamentoTest extends TestCase
{
    use RefreshDatabase;

    public function test_departamento_tiene_relacion_con_documento_y_personal(){
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
        //Verifica que las relaciones regresen colecciones de documentos y de personal
        $this->assertInstanceOf(Collection::class, $departamento->documento);
        $this->assertInstanceOf(document::class, $departamento->documento->first());
        $this->assertInstanceOf(Collection::class, $departamento->personal);
        $this->assertInstanceOf(Personal::class, $departamento->personal->first());
    }

    public function test_renderiza_correctamente_la_vista_de_periodos_administrador(){
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
        $user = User::create([
            'id' => 7777,
            'email' => 'test@itoaxaca.edu.mx',
            'password' => null,
            'IdPersonal' => $personal->IdPersonal,
        ]);
        Auth::login($user);

        DB::table('departamento')->insert([
            ['IdDepartamento' => 1, 'nombreDepartamento' => 'Departamento 1'],
            ['IdDepartamento' => 2, 'nombreDepartamento' => 'Departamento 2'],
        ]);
        $controller = new departamentoController();

        // Simular una solicitud
        $request = Request::create('/departamentos', 'GET');
        $response = $controller->index($request);

        // Verificar los datos devueltos
        $this->assertInstanceOf(InertiaResponse::class, $response);
        // Verificar contenido de la respuesta
        $responseContent = $response->toResponse($request)->getContent();
        $this->assertStringContainsString('Dashboard_admin_departamento', $responseContent);   
    }
    
    public function test_renderiza_correctamente_la_vista_de_periodos_secretaria(){
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

        DB::table('departamento')->insert([
            ['IdDepartamento' => 1, 'nombreDepartamento' => 'Departamento 1'],
            ['IdDepartamento' => 2, 'nombreDepartamento' => 'Departamento 2'],
        ]);
        $controller = new departamentoController();

        // Simular una solicitud
        $request = Request::create('/departamentos', 'GET');
        $response = $controller->index($request);

        // Verificar los datos devueltos
        $this->assertInstanceOf(InertiaResponse::class, $response);
        // Verificar contenido de la respuesta
        $responseContent = $response->toResponse($request)->getContent();
        $this->assertStringContainsString('Dashboard_secre_departamento', $responseContent);   
    }

    public function test_Nuevo_departamento_exitoso(){
         // Desactiva eventos para evitar que realmente se dispare el evento Registered
         Event::fake();

         // Crear una solicitud simulada
         $request = Request::create('/departamento', 'PUT', [
             'nombreDepartamento' => 'Nuevo Departamento',
         ]);
 
         $controller = new departamentoController();
 
         $controller->nuevoDepartamento($request);
 
         $this->assertDatabaseHas('departamento', [
             'nombreDepartamento' => 'Nuevo Departamento',
         ]);
 
    }
    public function test_Nuevo_Departamento_falla_por_no_ser_unico(){
        Departamento::create([
            'nombreDepartamento' => 'Nuevo Departamento',
        ]);
        $this->expectException(ValidationException::class);
        // Crear una solicitud simulada
        $request = Request::create('/nuevoDepartamento', 'PUT', [
            'nombreDepartamento' => 'Nuevo Departamento',
        ]);

        $controller = new departamentoController();

        $controller->nuevoDepartamento($request);
    }

    public function test_editar_departamento(){
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Nuevo Departamento',
        ]);
        
        $request = Request::create(route('departamento.editar'), 'PUT', [
            'idDepartamento' => $departamento->IdDepartamento,
            'nombreDepartamento' => 'Nuevo Departamento editado',
        ]);

        $controller = new departamentoController();

        $controller->editarDepartamento($request);
        $this->assertDatabaseHas('departamento', [
            'IdDepartamento' =>$departamento->IdDepartamento,
            'nombreDepartamento' => 'Nuevo Departamento editado',
        ]);
    }

    public function test_validar_departamento_no_unico(){
        Departamento::create([
            'nombreDepartamento' => 'Nuevo Departamento',
        ]);
        $this->expectException(ValidationException::class);
        // Crear una solicitud simulada
        $request = Request::create(route('validar.departamento'), 'POST', [
            'nombreDepartamento' => 'Nuevo Departamento',
        ]);

        $controller = new departamentoController();

        $controller->validarDepartamento($request);
    }

    public function test_borrar_departamento(){
        $departamento = Departamento::create([
            'IdDepartamento' => 1,
            'nombreDepartamento' => 'Departamento para borrar'
        ]);
        $request = Request::create(route('departamento.borrar'),'DELETE',[
            'idDepartamento' => $departamento->IdDepartamento,
        ]);
        $controller = new departamentoController();

        $controller->borrarDepartamento($request);

        $this->assertDatabaseMissing('departamento',[
            'IdDepartamento' => $departamento->IdDepartamenento,
            'NombreDepartamento' => 'Departamento para borrar'
        ]);
    }
}
