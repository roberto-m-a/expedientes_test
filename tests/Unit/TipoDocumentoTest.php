<?php

namespace Tests\Unit;

use App\Http\Controllers\tipoDocumentoController;
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
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Inertia\Response as InertiaResponse;


class TipoDocumentoTest extends TestCase
{
    use RefreshDatabase;

    public function test_tipo_documento_tiene_muchos_documentos(){
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
        $this->assertInstanceOf(Collection::class, $tipoDoc->documento);
        $this->assertInstanceOf(document::class, $tipoDoc->documento->first());
    }

    public function test_renderiza_correctamente_la_vista_de_tipos_de_documentos_administrador(){
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
        $administrador = Administrador::create([
            'IdPersonal' =>$personal->IdPersonal,
        ]);
        $user = User::create([
            'id' => 7777,
            'email' => 'test@itoaxaca.edu.mx',
            'password' => null,
            'IdPersonal' => $personal->IdPersonal,
        ]);
        Auth::login($user);

        $tipoDoc = TipoDocumento::create([
            'nombreTipoDoc' => 'Nuevo tipo documento',
        ]);

        $controller = new tipoDocumentoController();

        // Simular una solicitud
        $request = Request::create('/tipoDocumento', 'GET');
        $response = $controller->index($request);

        // Verificar los datos devueltos
        $this->assertInstanceOf(InertiaResponse::class, $response);
        // Verificar contenido de la respuesta
        $responseContent = $response->toResponse($request)->getContent();
        $this->assertStringContainsString('Dashboard_admin_tipoDoc', $responseContent);   
    }

    public function test_renderiza_correctamente_la_vista_de_tipos_de_documentos_secretaria(){
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
            'IdPersonal' =>$personal->IdPersonal,
        ]);
        $user = User::create([
            'id' => 7777,
            'email' => 'test@itoaxaca.edu.mx',
            'password' => null,
            'IdPersonal' => $personal->IdPersonal,
        ]);
        Auth::login($user);

        $tipoDoc = TipoDocumento::create([
            'nombreTipoDoc' => 'Nuevo tipo documento',
        ]);

        $controller = new tipoDocumentoController();

        // Simular una solicitud
        $request = Request::create('/tipoDocumento', 'GET');
        $response = $controller->index($request);

        // Verificar los datos devueltos
        $this->assertInstanceOf(InertiaResponse::class, $response);
        // Verificar contenido de la respuesta
        $responseContent = $response->toResponse($request)->getContent();
        $this->assertStringContainsString('Dashboard_secre_tipoDoc', $responseContent);   
    }

    public function test_Nuevo_tipo_de_documento_exitoso()
    {
        // Desactiva eventos para evitar que realmente se dispare el evento Registered
        Event::fake();

        // Crear una solicitud simulada
        $request = Request::create('/nuevoTipoDoc', 'PUT', [
            'nombreTipoDoc' => 'Nuevo tipo documento',
        ]);

        $controller = new tipoDocumentoController();

        $controller->nuevoTipoDoc($request);

        $this->assertDatabaseHas('tipo_documento', [
            'nombreTipoDoc' => 'Nuevo tipo documento',
        ]);

    }

    public function test_Nuevo_tipo_de_documento_falla_por_no_ser_unico()
    {
        TipoDocumento::create([
            'nombreTipoDoc' => 'Nuevo tipo documento',
        ]);

        $this->expectException(ValidationException::class);

        // Crear una solicitud simulada
        $request = Request::create('/tipoDoc', 'PUT', [
            'nombreTipoDoc' => 'Nuevo tipo documento',
        ]);

        $controller = new tipoDocumentoController();

        $controller->nuevoTipoDoc($request);
    }

    public function test_editar_tipo_documento(){
        $tipoDocumento =TipoDocumento::create([
            'nombreTipoDoc' => 'Nuevo tipo documento',
        ]);
        $request = Request::create(route('tipoDoc.editar'),'PUT',[
            'idtipoDoc' => $tipoDocumento->IdTipoDocumento,
            'nombreTipoDoc' => 'Nuevo tipo documento editado',
        ]);
        
        $controller = new tipoDocumentoController();

        $controller->editarTipoDoc($request);

        $this->assertDatabaseHas('tipo_documento', [
            'IdTipoDocumento' => $tipoDocumento->IdTipoDocumento,
            'nombreTipoDoc' => 'Nuevo tipo documento editado',
        ]);
    }

    public function test_borrar_tipo_documento(){
        $tipoDocumento =TipoDocumento::create([
            'nombreTipoDoc' => 'Nuevo tipo documento',
        ]);
        $request = Request::create(route('tipoDoc.borrar'),'DELETE',[
            'idtipoDoc' => $tipoDocumento->IdTipoDocumento,
        ]);
        
        $controller = new tipoDocumentoController();

        $controller->borrarTipoDoc($request);

        $this->assertDatabaseMissing('tipo_documento', [
            'IdTipoDocumento' => $tipoDocumento->IdTipoDocumento,
            'nombreTipoDoc' => 'Nuevo tipo documento',
        ]);
    }
}
