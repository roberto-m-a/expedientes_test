<?php

namespace Tests\Unit;

use App\Http\Controllers\documentController;
use App\Models\Administrador;
use App\Models\Departamento;
use App\Models\Docente;
use App\Models\document;
use App\Models\expediente;
use App\Models\PeriodoEscolar;
use App\Models\Personal;
use App\Models\TipoDocumento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Response as InertiaResponse;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;
    public function test_documento_tiene_relacion_con_otros_registros()
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

        $this->assertInstanceOf(Expediente::class, $document->expediente);
        $this->assertInstanceOf(TipoDocumento::class, $document->tipoDocumento);
        $this->assertInstanceOf(PeriodoEscolar::class, $document->periodoEscolar);
        $this->assertInstanceOf(Departamento::class, $document->departamento);
        $this->assertInstanceOf(User::class, $document->user);
    }

    public function test_renderiza_la_vista_de_subida_de_documento_secretaria()
    {
        DB::table('tipo_documento')->insert([
            ['IdTipoDocumento' => 1, 'nombreTipoDoc' => 'Tipo 1'],
            ['IdTipoDocumento' => 2, 'nombreTipoDoc' => 'Tipo 2'],
        ]);
        DB::table('departamento')->insert([
            ['IdDepartamento' => 1, 'nombreDepartamento' => 'Departamento 1'],
            ['IdDepartamento' => 2, 'nombreDepartamento' => 'Departamento 2'],
        ]);
        DB::table('personal')->insert([
            ['IdPersonal' => 1, 'Nombre' => 'Usuario Prueba 1', 'Apellidos' => 'Apellidos 1', 'Sexo' => 'Hombre', 'IdDepartamento' => 1],
            ['IdPersonal' => 2, 'Nombre' => 'Usuario Prueba 2', 'Apellidos' => 'Apellidos 2', 'Sexo' => 'Mujer', 'IdDepartamento' => 2],
        ]);

        DB::table('users')->insert([
            'id' => 1,
            'email' => 'prueba@example.com',
            'password' => bcrypt('password'),
            'IdPersonal' => 1
        ]);
        DB::table('secretaria')->insert(
            ['IdSecretaria' => 100, 'IdPersonal' => 1],
        );
        DB::table('docente')->insert([
            ['IdDocente' => 2, 'IdPersonal' => 2, 'GradoAcademico' => 'Licenciatura'],
        ]);

        DB::table('expediente')->insert([
            ['IdExpediente' => 2, 'IdDocente' => 2],
        ]);

        DB::table('periodo_escolar')->insert([
            ['IdPeriodoEscolar' => 1, 'nombre_corto' => '2023-2024', 'fechaInicio' => '2023-01-01', 'fechaTermino' => '2024-01-01'],
            ['IdPeriodoEscolar' => 2, 'nombre_corto' => '2022-2023', 'fechaInicio' => '2022-01-01', 'fechaTermino' => '2023-01-01'],
        ]);
        // Autenticar el usuario
        $user = User::find(1);
        $this->actingAs($user);
        // Crear una instancia del controlador
        $controller = new documentController();

        // Simular una solicitud
        $request = Request::create('/nuevoDocumento', 'GET');
        $response = $controller->index($request);

        // Verificar los datos devueltos
        $this->assertInstanceOf(InertiaResponse::class, $response);

        // Verificar contenido de la respuesta para el rol de Secretaría
        $responseContent = $response->toResponse($request)->getContent();
        $this->assertStringContainsString('Dashboard_secre_nuevoDocumento', $responseContent);
    }

    public function test_renderiza_la_vista_de_subida_de_documento_admin()
    {
        DB::table('tipo_documento')->insert([
            ['IdTipoDocumento' => 1, 'nombreTipoDoc' => 'Tipo 1'],
            ['IdTipoDocumento' => 2, 'nombreTipoDoc' => 'Tipo 2'],
        ]);
        DB::table('departamento')->insert([
            ['IdDepartamento' => 1, 'nombreDepartamento' => 'Departamento 1'],
            ['IdDepartamento' => 2, 'nombreDepartamento' => 'Departamento 2'],
        ]);
        DB::table('personal')->insert([
            ['IdPersonal' => 1, 'Nombre' => 'Usuario Prueba 1', 'Apellidos' => 'Apellidos 1', 'Sexo' => 'Hombre', 'IdDepartamento' => 1],
            ['IdPersonal' => 2, 'Nombre' => 'Usuario Prueba 2', 'Apellidos' => 'Apellidos 2', 'Sexo' => 'Mujer', 'IdDepartamento' => 2],
        ]);

        DB::table('users')->insert([
            'id' => 1,
            'email' => 'prueba@example.com',
            'password' => bcrypt('password'),
            'IdPersonal' => 1
        ]);
        DB::table('administrador')->insert(
            ['IdAdministrador' => 100, 'IdPersonal' => 1],
        );
        DB::table('docente')->insert([
            ['IdDocente' => 2, 'IdPersonal' => 2, 'GradoAcademico' => 'Licenciatura'],
        ]);

        DB::table('expediente')->insert([
            ['IdExpediente' => 2, 'IdDocente' => 2],
        ]);

        DB::table('periodo_escolar')->insert([
            ['IdPeriodoEscolar' => 1, 'nombre_corto' => '2023-2024', 'fechaInicio' => '2023-01-01', 'fechaTermino' => '2024-01-01'],
            ['IdPeriodoEscolar' => 2, 'nombre_corto' => '2022-2023', 'fechaInicio' => '2022-01-01', 'fechaTermino' => '2023-01-01'],
        ]);

        // Autenticar el usuario
        $user = User::find(1);
        $this->actingAs($user);
        // Crear una instancia del controlador
        $controller = new documentController();

        // Simular una solicitud
        $request = Request::create('/nuevoDocumento', 'GET');
        $response = $controller->index($request);

        // Verificar los datos devueltos
        $this->assertInstanceOf(InertiaResponse::class, $response);

        // Verificar contenido de la respuesta para el rol de Secretaría
        $responseContent = $response->toResponse($request)->getContent();
        $this->assertStringContainsString('Dashboard_admin_nuevoDocumento', $responseContent);
    }

    public function test_renderiza_la_vista_de_subida_de_documento_docente()
    {
        DB::table('tipo_documento')->insert([
            ['IdTipoDocumento' => 1, 'nombreTipoDoc' => 'Tipo 1'],
            ['IdTipoDocumento' => 2, 'nombreTipoDoc' => 'Tipo 2'],
        ]);
        DB::table('departamento')->insert([
            ['IdDepartamento' => 1, 'nombreDepartamento' => 'Departamento 1'],
            ['IdDepartamento' => 2, 'nombreDepartamento' => 'Departamento 2'],
        ]);
        DB::table('personal')->insert([
            ['IdPersonal' => 1, 'Nombre' => 'Usuario Prueba 1', 'Apellidos' => 'Apellidos 1', 'Sexo' => 'Hombre', 'IdDepartamento' => 1],
            ['IdPersonal' => 2, 'Nombre' => 'Usuario Prueba 2', 'Apellidos' => 'Apellidos 2', 'Sexo' => 'Mujer', 'IdDepartamento' => 2],
        ]);

        DB::table('users')->insert([
            'id' => 1,
            'email' => 'prueba@example.com',
            'password' => bcrypt('password'),
            'IdPersonal' => 1
        ]);
        DB::table('docente')->insert([
            ['IdDocente' => 1, 'IdPersonal' => 1, 'GradoAcademico' => 'Maestria'],
            ['IdDocente' => 2, 'IdPersonal' => 2, 'GradoAcademico' => 'Licenciatura'],
        ]);

        DB::table('expediente')->insert([
            ['IdExpediente' => 1, 'IdDocente' => 1],
            ['IdExpediente' => 2, 'IdDocente' => 2],
        ]);

        DB::table('periodo_escolar')->insert([
            ['IdPeriodoEscolar' => 1, 'nombre_corto' => '2023-2024', 'fechaInicio' => '2023-01-01', 'fechaTermino' => '2024-01-01'],
            ['IdPeriodoEscolar' => 2, 'nombre_corto' => '2022-2023', 'fechaInicio' => '2022-01-01', 'fechaTermino' => '2023-01-01'],
        ]);

        // Autenticar el usuario
        $user = User::find(1);
        $this->actingAs($user);
        // Crear una instancia del controlador
        $controller = new documentController();

        // Simular una solicitud
        $request = Request::create('/nuevoDocumento', 'GET');
        $response = $controller->index($request);

        // Verificar los datos devueltos
        $this->assertInstanceOf(InertiaResponse::class, $response);

        // Verificar contenido de la respuesta para el rol de Secretaría
        $responseContent = $response->toResponse($request)->getContent();
        $this->assertStringContainsString('Dashboard_subirDocumento', $responseContent);
    }

    public function test_renderiza_la_vista_de_todos_los_documentos_administrador()
    {
        DB::table('tipo_documento')->insert([
            ['IdTipoDocumento' => 1, 'nombreTipoDoc' => 'Tipo 1'],
            ['IdTipoDocumento' => 2, 'nombreTipoDoc' => 'Tipo 2'],
        ]);
        DB::table('departamento')->insert([
            ['IdDepartamento' => 1, 'nombreDepartamento' => 'Departamento 1'],
            ['IdDepartamento' => 2, 'nombreDepartamento' => 'Departamento 2'],
        ]);
        DB::table('personal')->insert([
            ['IdPersonal' => 1, 'Nombre' => 'Usuario Prueba 1', 'Apellidos' => 'Apellidos 1', 'Sexo' => 'Hombre', 'IdDepartamento' => 1],
            ['IdPersonal' => 2, 'Nombre' => 'Usuario Prueba 2', 'Apellidos' => 'Apellidos 2', 'Sexo' => 'Mujer', 'IdDepartamento' => 2],
        ]);

        DB::table('users')->insert([
            'id' => 1,
            'email' => 'prueba@example.com',
            'password' => bcrypt('password'),
            'IdPersonal' => 1
        ]);
        DB::table('administrador')->insert(
            ['IdAdministrador' => 100, 'IdPersonal' => 1],
        );
        DB::table('docente')->insert([
            ['IdDocente' => 2, 'IdPersonal' => 2, 'GradoAcademico' => 'Licenciatura'],
        ]);

        DB::table('expediente')->insert([
            ['IdExpediente' => 2, 'IdDocente' => 2],
        ]);
        DB::table('periodo_escolar')->insert([
            ['IdPeriodoEscolar' => 1, 'nombre_corto' => '2023-2024', 'fechaInicio' => '2023-01-01', 'fechaTermino' => '2024-01-01'],
            ['IdPeriodoEscolar' => 2, 'nombre_corto' => '2022-2023', 'fechaInicio' => '2022-01-01', 'fechaTermino' => '2023-01-01'],
        ]);

        DB::table('documento')->insert([
            [
                'Titulo' => 'Document Title',
                'fechaExpedicion' => '2024-08-06',
                'fechaEntrega' => '2024-08-07',
                'Estatus' => 'Entregado',
                'region' => 'Externo',
                'IdTipoDocumento' => 1,
                'IdPeriodoEscolar' => 1,
                'IdExpediente' => 2,
                'IdDepartamento' => null,
                'IdUsuario' => 1,
                'URL' => 'http://example.com',
                'Dependencia' => 'Dependencia 1',
            ],
            [
                'Titulo' => 'Document Title',
                'fechaExpedicion' => '2024-08-06',
                'fechaEntrega' => '2024-08-07',
                'Estatus' => 'En proceso',
                'region' => 'Interno',
                'IdTipoDocumento' => 1,
                'IdPeriodoEscolar' => 1,
                'IdExpediente' => 2,
                'IdDepartamento' => 1,
                'IdUsuario' => 1,
                'URL' => 'http://example.com',
                'Dependencia' => '',
            ]
        ]);
        // Autenticar el usuario
        $user = User::find(1);
        $this->actingAs($user);
        // Crear una instancia del controlador
        $controller = new documentController();

        // Simular una solicitud
        $request = Request::create('/documentos', 'GET');
        $response = $controller->documentsIndex($request);

        // Verificar los datos devueltos
        $this->assertInstanceOf(InertiaResponse::class, $response);

        // Verificar contenido de la respuesta para el rol de Secretaría
        $responseContent = $response->toResponse($request)->getContent();
        $this->assertStringContainsString('Dashboard_admin_documentos', $responseContent);
    }

    public function test_renderiza_la_vista_de_todos_los_documentos_secretaria()
    {
        DB::table('tipo_documento')->insert([
            ['IdTipoDocumento' => 1, 'nombreTipoDoc' => 'Tipo 1'],
            ['IdTipoDocumento' => 2, 'nombreTipoDoc' => 'Tipo 2'],
        ]);
        DB::table('departamento')->insert([
            ['IdDepartamento' => 1, 'nombreDepartamento' => 'Departamento 1'],
            ['IdDepartamento' => 2, 'nombreDepartamento' => 'Departamento 2'],
        ]);
        DB::table('personal')->insert([
            ['IdPersonal' => 1, 'Nombre' => 'Usuario Prueba 1', 'Apellidos' => 'Apellidos 1', 'Sexo' => 'Hombre', 'IdDepartamento' => 1],
            ['IdPersonal' => 2, 'Nombre' => 'Usuario Prueba 2', 'Apellidos' => 'Apellidos 2', 'Sexo' => 'Mujer', 'IdDepartamento' => 2],
        ]);

        DB::table('users')->insert([
            'id' => 1,
            'email' => 'prueba@example.com',
            'password' => bcrypt('password'),
            'IdPersonal' => 1
        ]);
        DB::table('secretaria')->insert(
            ['IdSecretaria' => 100, 'IdPersonal' => 1],
        );
        DB::table('docente')->insert([
            ['IdDocente' => 2, 'IdPersonal' => 2, 'GradoAcademico' => 'Licenciatura'],
        ]);

        DB::table('expediente')->insert([
            ['IdExpediente' => 2, 'IdDocente' => 2],
        ]);
        DB::table('periodo_escolar')->insert([
            ['IdPeriodoEscolar' => 1, 'nombre_corto' => '2023-2024', 'fechaInicio' => '2023-01-01', 'fechaTermino' => '2024-01-01'],
            ['IdPeriodoEscolar' => 2, 'nombre_corto' => '2022-2023', 'fechaInicio' => '2022-01-01', 'fechaTermino' => '2023-01-01'],
        ]);

        DB::table('documento')->insert([
            [
                'Titulo' => 'Document Title',
                'fechaExpedicion' => '2024-08-06',
                'fechaEntrega' => '2024-08-07',
                'Estatus' => 'Entregado',
                'region' => 'Externo',
                'IdTipoDocumento' => 1,
                'IdPeriodoEscolar' => 1,
                'IdExpediente' => 2,
                'IdDepartamento' => null,
                'IdUsuario' => 1,
                'URL' => 'http://example.com',
                'Dependencia' => 'Dependencia 1',
            ],
            [
                'Titulo' => 'Document Title',
                'fechaExpedicion' => '2024-08-06',
                'fechaEntrega' => '2024-08-07',
                'Estatus' => 'En proceso',
                'region' => 'Interno',
                'IdTipoDocumento' => 1,
                'IdPeriodoEscolar' => 1,
                'IdExpediente' => 2,
                'IdDepartamento' => 1,
                'IdUsuario' => 1,
                'URL' => 'http://example.com',
                'Dependencia' => '',
            ]
        ]);
        // Autenticar el usuario
        $user = User::find(1);
        $this->actingAs($user);
        // Crear una instancia del controlador
        $controller = new documentController();

        // Simular una solicitud
        $request = Request::create('/documentos', 'GET');
        $response = $controller->documentsIndex($request);

        // Verificar los datos devueltos
        $this->assertInstanceOf(InertiaResponse::class, $response);

        // Verificar contenido de la respuesta para el rol de Secretaría
        $responseContent = $response->toResponse($request)->getContent();
        $this->assertStringContainsString('Dashboard_secre_documentos', $responseContent);
    }

    public function crearUsuario()
    {
        $departamento = Departamento::create([
            'IdDepartamento' => 1234,
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Virginia',
            'Apellidos' => 'Ortiz',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Mujer',
        ]);
        $administrador = Administrador::create([
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $user = User::create([
            'id' => 7777,
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personal->IdPersonal,
        ]);
        $this->actingAs($user);
        //ExpedienteDocente
        $personalDocente = Personal::create([
            'Nombre' => 'Panfilo',
            'Apellidos' => 'Ramirez',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);

        $docente = Docente::create([
            'IdPersonal' => $personalDocente->IdPersonal,
            'GradoAcademico' => 'Licenciatura',
        ]);

        $expediente = expediente::create([
            'IdExpediente' => 1,
            'IdDocente' => $docente->IdDocente,
            'numDocumentos' =>2,
        ]);

        $tipoDoc = TipoDocumento::create([
            'nombreTipoDoc' => 'Sistemas',
        ]);

        $periodoEscolar = PeriodoEscolar::create([
            'fechaInicio' => '2024-07-01',
            'fechaTermino' => '2024-07-01',
            'nombre_corto' => 'ENE-JUN 2024',
        ]);
        return [$departamento, $personal, $administrador, $user, $expediente, $tipoDoc, $periodoEscolar];
    }
    public function test_subir_documento_falla_por_no_ser_pdf()
    {
        $crearUsuario = $this->crearUsuario();

        $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Debes de ingresar un archivo PDF');
        $request = Request::create(route('registrar.documento'), 'POST', [
            'Expediente' => $crearUsuario[4]->IdExpediente,
            'TipoDocumento' => $crearUsuario[5]->IdTipoDocumento,
            'Titulo' => 'Title doc example',
            'FechaExpedicion' => '2024-02-01',
            'Region' => 'Interno',
            'PeriodoEscolar' => $crearUsuario[6]->IdPeriodoEscolar,
            'Archivo' => $file,
        ]);
        $controller = new documentController();
        $controller->nuevoDocumento($request);
    }
    public function test_subir_documento_falla_por_fechas_incorrectas()
    {
        $crearUsuario = $this->crearUsuario();

        $file = UploadedFile::fake()->create('document.pdf', 100, 'text/plain');
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Las fecha de expedición no puede ser despues de la fecha de entrega');
        $request = Request::create(route('registrar.documento'), 'POST', [
            'Expediente' => $crearUsuario[4]->IdExpediente,
            'TipoDocumento' => $crearUsuario[5]->IdTipoDocumento,
            'Titulo' => 'Title doc example',
            'FechaExpedicion' => '2024-02-01',
            'Region' => 'Interno',
            'Departamento' => $crearUsuario[0]->IdDepartamento,
            'Estatus' => 'Entregado',
            'PeriodoEscolar' => $crearUsuario[6]->IdPeriodoEscolar,
            'Archivo' => $file,
            'FechaEntrega' => '2024-01-01',
        ]);
        $controller = new documentController();
        $controller->nuevoDocumento($request);
    }

    public function test_subir_documento_correctamente()
    {
        $crearUsuario = $this->crearUsuario();

        $file = UploadedFile::fake()->create('document.pdf', 100, 'text/plain');

        $request = Request::create(route('registrar.documento'), 'POST', [
            'Expediente' => ['IdExpediente' => $crearUsuario[4]->IdExpediente],
            'TipoDocumento' => ['IdTipoDocumento' => $crearUsuario[5]->IdTipoDocumento],
            'Titulo' => 'Title doc example',
            'FechaExpedicion' => '2024-02-01',
            'Region' => 'Externo',
            'Estatus' => 'Entregado',
            'Dependencia' => 'Dependencia X',
            'PeriodoEscolar' => ['IdPeriodoEscolar' => $crearUsuario[6]->IdPeriodoEscolar],
            'Archivo' => $file,
            'FechaEntrega' => '2024-03-01',
        ]);
        $controller = new documentController();
        $controller->nuevoDocumento($request);

        $this->assertDatabaseHas('documento', [
            'IdExpediente' => $crearUsuario[4]->IdExpediente,
            'IdTipoDocumento' => $crearUsuario[5]->IdTipoDocumento,
            'Titulo' => 'Title doc example',
            'fechaExpedicion' => '2024-02-01',
            'region' => 'Externo',
            'dependencia' => 'Dependencia X',
            'IdPeriodoEscolar' => $crearUsuario[6]->IdPeriodoEscolar,
            'fechaEntrega' => '2024-03-01',
            'IdUsuario' => $crearUsuario[3]->id,
        ]);
    }

    public function test_validar_documento_falla_por_no_ser_pdf()
    {
        $crearUsuario = $this->crearUsuario();

        $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Debes de ingresar un archivo PDF');
        $request = Request::create(route('registrar.documento'), 'POST', [
            'Expediente' => $crearUsuario[4]->IdExpediente,
            'TipoDocumento' => $crearUsuario[5]->IdTipoDocumento,
            'Titulo' => 'Title doc example',
            'FechaExpedicion' => '2024-02-01',
            'Region' => 'Interno',
            'PeriodoEscolar' => $crearUsuario[6]->IdPeriodoEscolar,
            'Archivo' => $file,
        ]);
        $controller = new documentController();
        $controller->validarDocumento($request);
    }
    public function test_validar_documento_falla_por_fechas_incorrectas()
    {
        $crearUsuario = $this->crearUsuario();
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Las fecha de expedición no puede ser despues de la fecha de entrega');
        $request = Request::create(route('registrar.documento'), 'POST', [
            'Expediente' => $crearUsuario[4]->IdExpediente,
            'TipoDocumento' => $crearUsuario[5]->IdTipoDocumento,
            'Titulo' => 'Title doc example',
            'FechaExpedicion' => '2024-02-01',
            'Region' => 'Interno',
            'Departamento' => $crearUsuario[0]->IdDepartamento,
            'Estatus' => 'Entregado',
            'PeriodoEscolar' => $crearUsuario[6]->IdPeriodoEscolar,

            'FechaEntrega' => '2024-01-01',
        ]);
        $controller = new documentController();
        $controller->validarDocumento($request);
    }

    public function test_validar_documento_correctamente()
    {
        $crearUsuario = $this->crearUsuario();

        $file = UploadedFile::fake()->create('document.pdf', 100, 'text/plain');

        $request = Request::create(route('registrar.documento'), 'POST', [
            'Expediente' => ['IdExpediente' => $crearUsuario[4]->IdExpediente],
            'TipoDocumento' => ['IdTipoDocumento' => $crearUsuario[5]->IdTipoDocumento],
            'Titulo' => 'Title doc example',
            'FechaExpedicion' => '2024-02-01',
            'Region' => 'Externo',
            'Estatus' => 'Entregado',
            'Dependencia' => 'Dependencia X',
            'PeriodoEscolar' => ['IdPeriodoEscolar' => $crearUsuario[6]->IdPeriodoEscolar],
            'Archivo' => $file,
            'FechaEntrega' => '2024-03-01',
        ]);
        $controller = new documentController();
        try {
            $controller->validarDocumento($request);
        } catch (ValidationException $e) {
            $this->fail('No se esperaba una excepción de validación, pero se lanzó una: ' . $e->getMessage());
        }
        $this->assertTrue(true); //se hace esta asercion para saber si llego y no se quedo en el catch
    }

    public function test_editar_documento_correctamente() {
        $crearUsuario = $this->crearUsuario();
    
        // Simular archivo PDF y subirlo al almacenamiento
        Storage::fake('public'); // Simula el sistema de archivos
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
    
        // Subir el archivo al storage
        $path = Storage::putFileAs('public/documents', $file, '2024-08-06_12_00_00_document.pdf');
        $url = asset('storage/documents/2024-08-06_12_00_00_document.pdf');
    
        $crearUsuario[4]->numDocumentos = 2;
        $crearUsuario[4]->save();

        // Crear documento inicial en la base de datos
        $document = document::create([
            'Titulo' => 'Document Title',
            'fechaExpedicion' => '2024-08-06',
            'fechaEntrega' => '2024-08-07',
            'Estatus' => 'Entregado',
            'region' => 'Interno',
            'IdTipoDocumento' => $crearUsuario[5]->IdTipoDocumento,
            'IdPeriodoEscolar' => $crearUsuario[6]->IdPeriodoEscolar,
            'IdExpediente' => $crearUsuario[4]->IdExpediente,
            'IdDepartamento' => $crearUsuario[0]->IdDepartamento,
            'IdUsuario' => $crearUsuario[3]->id,
            'URL' => $url, // URL que apunta al archivo subido
            'Dependencia' => '',
        ]);
    
        $URLAntiguo = $document->URL;
    
        // Crear nuevo tipo de documento
        $nuevoTipoDoc = TipoDocumento::create([
            'nombreTipoDoc' => 'nuevo departamento'
        ]);
    
        // Crear nuevo expediente
        $personalDocente = Personal::create([
            'Nombre' => 'Alfonso',
            'Apellidos' => 'Perez',
            'IdDepartamento' => $crearUsuario[0]->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
    
        $docenteN = Docente::create([
            'IdPersonal' => $personalDocente->IdPersonal,
            'GradoAcademico' => 'Licenciatura',
        ]);
    
        $expedienteN = expediente::create([
            'IdExpediente' => 132246,
            'IdDocente' => $docenteN->IdDocente,
        ]);
    
        // Simular nuevo archivo PDF que será subido en la edición
        $nuevoFile = UploadedFile::fake()->create('new_document.pdf', 100, 'application/pdf');
    
        $request = Request::create(route('documento.editar'), 'POST', [
            'IdDocumento' => $document->IdDocumento,
            'Expediente' => ['IdExpediente' => $expedienteN->IdExpediente],
            'TipoDocumento' => ['IdTipoDocumento' => $nuevoTipoDoc->IdTipoDocumento],
            'Titulo' => 'Title doc example edit',
            'FechaExpedicion' => '2024-03-01',
            'Region' => 'Externo',
            'Estatus' => 'Entregado',
            'Dependencia' => 'Dependencia X',
            'PeriodoEscolar' => ['IdPeriodoEscolar' => $crearUsuario[6]->IdPeriodoEscolar],
            'Archivo' => $nuevoFile,
            'FechaEntrega' => '2024-03-15',
        ]);
    
        $controller = new documentController();
        $controller->editarDocumento($request);
    
        // Verifica que el documento fue actualizado en la base de datos
        $this->assertDatabaseHas('documento', [
            'IdExpediente' => $expedienteN->IdExpediente,
            'IdTipoDocumento' => $nuevoTipoDoc->IdTipoDocumento,
            'Titulo' => 'Title doc example edit',
            'fechaExpedicion' => '2024-03-01',
            'region' => 'Externo',
            'Estatus' => 'Entregado',
            'Dependencia' => 'Dependencia X',
            'IdDepartamento' => null,
            'IdPeriodoEscolar' => $crearUsuario[6]->IdPeriodoEscolar,
            'fechaEntrega' => '2024-03-15',
            'IdUsuario' => $crearUsuario[3]->id,
        ]);
    
        // Verifica que el URL del documento fue modificado (no es el mismo que antes)
        $this->assertNotEquals($document->fresh()->URL, $URLAntiguo);
    }

    public function test_validar_entrega_falla_fechas_incorrectas(){
        $crearUsuario = $this->crearUsuario();
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Las fecha de entrega no puede ser antes de la fecha de expedición');
        // Simular archivo PDF y subirlo al almacenamiento
        Storage::fake('public'); // Simula el sistema de archivos
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
    
        // Subir el archivo al storage
        $path = Storage::putFileAs('public/documents', $file, '2024-08-06_12_00_00_document.pdf');
        $url = asset('storage/documents/2024-08-06_12_00_00_document.pdf');
    
        $crearUsuario[4]->numDocumentos = 2;
        $crearUsuario[4]->save();
        // Crear documento inicial en la base de datos
        $document = document::create([
            'Titulo' => 'Document Title',
            'fechaExpedicion' => '2024-08-06',
            'fechaEntrega' => null,
            'Estatus' => 'Entregado',
            'region' => 'Interno',
            'IdTipoDocumento' => $crearUsuario[5]->IdTipoDocumento,
            'IdPeriodoEscolar' => $crearUsuario[6]->IdPeriodoEscolar,
            'IdExpediente' => $crearUsuario[4]->IdExpediente,
            'IdDepartamento' => $crearUsuario[0]->IdDepartamento,
            'IdUsuario' => $crearUsuario[3]->id,
            'URL' => $url, // URL que apunta al archivo subido
            'Dependencia' => '',
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 100, 'text/plain');
        
        $request = Request::create(route('validar.entrega'), 'POST', [
            'IdDocumento' => $document->IdDocumento,
            'FechaExpedicion' => $document->fechaExpedicion,
            'FechaEntrega' => '2024-08-01',
            'Archivo' => $file,
        ]);
        $controller = new documentController();
        $controller->validarEntrega($request);
    }

    public function test_validar_entrega_falla_por_no_ser_PDF(){
        $crearUsuario = $this->crearUsuario();
        // Simular archivo PDF y subirlo al almacenamiento
        Storage::fake('public'); // Simula el sistema de archivos
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
    
        // Subir el archivo al storage
        $path = Storage::putFileAs('public/documents', $file, '2024-08-06_12_00_00_document.pdf');
        $url = asset('storage/documents/2024-08-06_12_00_00_document.pdf');
    
        $crearUsuario[4]->numDocumentos = 2;
        $crearUsuario[4]->save();
        // Crear documento inicial en la base de datos
        $document = document::create([
            'Titulo' => 'Document Title',
            'fechaExpedicion' => '2024-03-01',
            'fechaEntrega' => null,
            'Estatus' => 'Entregado',
            'region' => 'Interno',
            'IdTipoDocumento' => $crearUsuario[5]->IdTipoDocumento,
            'IdPeriodoEscolar' => $crearUsuario[6]->IdPeriodoEscolar,
            'IdExpediente' => $crearUsuario[4]->IdExpediente,
            'IdDepartamento' => $crearUsuario[0]->IdDepartamento,
            'IdUsuario' => $crearUsuario[3]->id,
            'URL' => $url, // URL que apunta al archivo subido
            'Dependencia' => '',
        ]);
        $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Debes de ingresar un archivo PDF');
        $request = Request::create(route('validar.entrega'), 'POST', [
            'IdDocumento' => $document->IdDocumento,
            'FechaEntrega' => '2024-04-01',
            'FechaExpedicion' => $document->fechaExpedicion,
            'Archivo' => $file,
        ]);
        $controller = new documentController();
        $controller->validarEntrega($request);
    }

    public function test_validar_entrega_correctamente(){
        $crearUsuario = $this->crearUsuario();
        // Simular archivo PDF y subirlo al almacenamiento
        Storage::fake('public'); // Simula el sistema de archivos
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
    
        // Subir el archivo al storage
        $path = Storage::putFileAs('public/documents', $file, '2024-08-06_12_00_00_document.pdf');
        $url = asset('storage/documents/2024-08-06_12_00_00_document.pdf');
    
        $crearUsuario[4]->numDocumentos = 2;
        $crearUsuario[4]->save();
        // Crear documento inicial en la base de datos
        $document = document::create([
            'Titulo' => 'Document Title',
            'fechaExpedicion' => '2024-02-06',
            'fechaEntrega' => null,
            'Estatus' => 'Entregado',
            'region' => 'Interno',
            'IdTipoDocumento' => $crearUsuario[5]->IdTipoDocumento,
            'IdPeriodoEscolar' => $crearUsuario[6]->IdPeriodoEscolar,
            'IdExpediente' => $crearUsuario[4]->IdExpediente,
            'IdDepartamento' => $crearUsuario[0]->IdDepartamento,
            'IdUsuario' => $crearUsuario[3]->id,
            'URL' => $url, // URL que apunta al archivo subido
            'Dependencia' => '',
        ]);
        $file = UploadedFile::fake()->create('document.pdf', 100, 'text/plain');
        $request = Request::create(route('validar.entrega'), 'POST', [
            'IdDocumento' => $document->IdDocumento,
            'FechaEntrega' => '2024-03-01',
            'FechaExpedicion' => $document->fechaExpedicion,
            'Archivo' => $file,
        ]);
        $controller = new documentController();
        try {
            $controller->validarEntrega($request);
        } catch (ValidationException $e) {
            $this->fail('No se esperaba una excepción de validación, pero se lanzó una: ' . $e->getMessage());
        }
        $this->assertTrue(true); //
    }

    public function test_entregar_documento_correctamente(){
        $crearUsuario = $this->crearUsuario();
        // Simular archivo PDF y subirlo al almacenamiento
        Storage::fake('public'); // Simula el sistema de archivos
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
    
        // Subir el archivo al storage
        $path = Storage::putFileAs('public/documents', $file, '2024-08-06_12_00_00_document.pdf');
        $url = asset('storage/documents/2024-08-06_12_00_00_document.pdf');
    
        $crearUsuario[4]->numDocumentos = 2;
        $crearUsuario[4]->save();
        // Crear documento inicial en la base de datos
        $document = document::create([
            'Titulo' => 'Document Title',
            'fechaExpedicion' => '2024-02-06',
            'fechaEntrega' => null,
            'Estatus' => 'Entregado',
            'region' => 'Interno',
            'IdTipoDocumento' => $crearUsuario[5]->IdTipoDocumento,
            'IdPeriodoEscolar' => $crearUsuario[6]->IdPeriodoEscolar,
            'IdExpediente' => $crearUsuario[4]->IdExpediente,
            'IdDepartamento' => $crearUsuario[0]->IdDepartamento,
            'IdUsuario' => $crearUsuario[3]->id,
            'URL' => $url, // URL que apunta al archivo subido
            'Dependencia' => '',
        ]);
        $URLAntiguo = $document->URL;
        $file = UploadedFile::fake()->create('document.pdf', 100, 'text/plain');
        $request = Request::create(route('entregar.documento'), 'POST', [
            'IdDocumento' => $document->IdDocumento,
            'FechaEntrega' => '2024-03-01',
            'FechaExpedicion' => $document->fechaExpedicion,
            'Archivo' => $file,
        ]);
        $controller = new documentController();
        $controller->entregarDocumento($request);
        $this->assertDatabaseHas('documento',[
            'IdDocumento' => $document->IdDocumento,
            'fechaEntrega' =>'2024-03-01',
        ]);
        $this->assertNotEquals($document->fresh()->URL, $URLAntiguo);
    }
}
