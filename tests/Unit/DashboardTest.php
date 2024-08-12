<?php

namespace Tests\Unit;

use App\Http\Controllers\DashboardController;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Inertia\Response as InertiaResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_Retorna_el_dashboard_al_docente()
    {
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
        $periodoEscolar = PeriodoEscolar::create([
            'fechaInicio' => '2024-01-01',
            'fechaTermino' =>  '2024-06-06',
            'nombre_corto' => 'ENE-JUN 2024',
        ]);
        $docente = Docente::create([
            'IdPersonal' => $personal->IdPersonal,
            'GradoAcademico' => 'Licenciatura', // Agrega los campos necesarios
        ]);

        $expediente = expediente::create([
            'IdDocente' => $docente->IdDocente,
            'numDocumentos' => 0, // Agrega los campos necesarios
        ]);

        // Crear tipos de documentos
        $tipo_documentos = [
            ['IdTipoDocumento' => 1, 'nombreTipoDoc' => 'Tipo A'],
            ['IdTipoDocumento' => 2, 'nombreTipoDoc' => 'Tipo B'],
            ['IdTipoDocumento' => 3, 'nombreTipoDoc' => 'Tipo C'],
        ];

        foreach ($tipo_documentos as $tipo_documento) {
            TipoDocumento::create($tipo_documento);
        }

        // Crear documentos
        foreach ($tipo_documentos as $tipo_documento) {
            document::create([
                'IdExpediente' => $expediente->IdExpediente,
                'IdTipoDocumento' => $tipo_documento['IdTipoDocumento'],
                'Titulo' => 'ExampleTitle',
                'fechaExpedicion' => '2024-06-06',
                'fechaEntrega' => '2024-06-07',
                'Estatus' => 'Entregado',
                'region' => 'Interno',
                'IdPeriodoEscolar' => $periodoEscolar->IdPeriodoEscolar,
                'IdDepartamento' => $departamento->IdDepartamento,
                'IdUsuario' => $user->id,
                'URL' => '/public/example',
                'Dependencia' => '',
            ]);
        }

        // Crear una instancia del controlador
        $controller = new DashboardController();

        // Simular una solicitud
        $request = Request::create('/dashboard', 'GET');
        $response = $controller->index($request);

        // Verificar los datos devueltos
        $this->assertInstanceOf(InertiaResponse::class, $response);
        // Verificar contenido de la respuesta
        $responseContent = $response->toResponse($request)->getContent();
        $this->assertStringContainsString('Cantidad de documentos en mi expediente', $responseContent);
    }
    public function test_Retorna_el_dashboard_a_la_secretaria()
    {
        PeriodoEscolar::create([
            'IdPeriodoEscolar' => 1, 'nombre_corto' => '2023-2024', 'fechaInicio' => '2023-01-01', 'fechaTermino' => '2024-01-01'
        ]);
        TipoDocumento::create([
            'IdTipoDocumento' => 1, 'nombreTipoDoc' => 'Tipo 1'
        ]);
        $departamento = Departamento::create([
            'IdDepartamento' => 1234,
            'nombreDepartamento' => 'Sistemas',
        ]);
        //Agregamos al personal de Secretaria
        $personalSecretaria = Personal::create([
            'IdPersonal' => 778,
            'Nombre' => 'Virginia',
            'Apellidos' => 'Ortiz',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo',
        ]);
        $secretaria = Secretaria::create([
            'IdPersonal' => $personalSecretaria->IdPersonal,
        ]);
        $user = User::create([
            'id' => 7777,
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personalSecretaria->IdPersonal,
        ]);
        Auth::login($user);//logueamos al usuario de la secretaria

        $personalDocente = Personal::create([
            'IdPersonal' => 1000,
            'Nombre' => 'Monica',
            'Apellidos' => 'Galindo',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo'=> 'Mujer',
        ]);
        $docente = Docente::create([
            
            'IdPersonal' => $personalDocente->IdPersonal,
            'GradoAcademico' => 'Licenciatura',
        ]);
        Expediente::create([
            'IdDocente' => $docente->IdDocente,
            'numDocumentos' => 0,
        ]);
        // Crear una instancia del controlador
        $controller = new DashboardController();

        // Simular una solicitud
        $request = Request::create('/dashboard', 'GET');
        $response = $controller->index($request);

        // Verificar los datos devueltos
        $this->assertInstanceOf(InertiaResponse::class, $response);

        // Verificar contenido de la respuesta para el rol de Secretaría
        $responseContent = $response->toResponse($request)->getContent();
        $this->assertStringContainsString('Cantidad de documentos por cada tipo de documento', $responseContent);
        $this->assertStringContainsString('Dashboard_secretaria', $responseContent);
    }
    public function test_Retorna_el_dashboard_al_administrador()
    {
        $departamento = Departamento::create([
            'IdDepartamento' => 1234,
            'nombreDepartamento' => 'Sistemas',
        ]);
        //Se crea un Administrador de prueba
        $personalAdmin = Personal::create([
            'IdPersonal' => 779,
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo',
        ]);
        $administrador = Administrador::create([
            'IdPersonal' => $personalAdmin->IdPersonal,
        ]);
        $user = User::create([
            'id' => 7777,
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personalAdmin->IdPersonal,
        ]);
        Auth::login($user);
        // Crear una instancia del controlador
        $controller = new DashboardController();

        // Simular una solicitud
        $request = Request::create('/dashboard', 'GET');
        $response = $controller->index($request);

        // Verificar los datos devueltos
        $this->assertInstanceOf(InertiaResponse::class, $response);
        // Verificar contenido de la respuesta
        $responseContent = $response->toResponse($request)->getContent();
        $this->assertStringContainsString('Cantidad de documentos por cada tipo de documento', $responseContent);
        $this->assertStringContainsString('Dashboard_admin', $responseContent);
    }

    public function testFiltrarConsultaSinRegistros()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        // Crear datos necesarios para la prueba
        DB::table('tipo_documento')->insert([
            ['IdTipoDocumento' => 1, 'nombreTipoDoc' => 'Tipo 1'],
            ['IdTipoDocumento' => 2, 'nombreTipoDoc' => 'Tipo 2'],
        ]);

        // Crear usuarios y personal
        DB::table('personal')->insert([
            ['IdPersonal' => 1, 'Nombre' => 'Usuario Prueba', 'Apellidos' => 'Apellidos 1','Sexo' => 'Hombre'],
        ]);

        DB::table('users')->insert([
            'id' => 1,
            'email' => 'prueba@example.com',
            'password' => bcrypt('password'),
            'IdPersonal' => 1
        ]);

        // Autenticar el usuario
        $user = User::find(1);
        Auth::login($user);

        // Realizar la solicitud
        $response = $this->post(route('filtrar.consulta'), [
            'TipoDocumento' => null,
            'PeriodoEscolar' => null,
            'Departamento' => null,
            'Region' => 'Todos',
            'Estatus' => 'Todos'
        ]);

        // Verificar la respuesta
        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('sinRegistros', 'Al parecer no hay registros con los parámetros ingresados');
    }

    public function testFiltrarConsultaConRegistros_administrador()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // Crear datos necesarios para la prueba
        DB::table('tipo_documento')->insert([
            ['IdTipoDocumento' => 1, 'nombreTipoDoc' => 'Tipo 1'],
            ['IdTipoDocumento' => 2, 'nombreTipoDoc' => 'Tipo 2'],
        ]);

        DB::table('personal')->insert([
            ['IdPersonal' => 1, 'Nombre' => 'Usuario Prueba 1', 'Apellidos' => 'Apellidos 1', 'Sexo' => 'Hombre'],
            ['IdPersonal' => 2, 'Nombre' => 'Usuario Prueba 2', 'Apellidos' => 'Apellidos 2', 'Sexo' => 'Mujer'],
        ]);

        DB::table('users')->insert([
            'id' => 1,
            'email' => 'prueba@example.com',
            'password' => bcrypt('password'),
            'IdPersonal' => 1
        ]);
        DB::table('administrador')->insert(
            ['IdAdministrador'=> 100,'IdPersonal' =>1],
        );
        DB::table('docente')->insert([
            ['IdDocente' => 2, 'IdPersonal' => 2, 'GradoAcademico' => 'Licenciatura'],
        ]);

        DB::table('expediente')->insert([
            ['IdExpediente' => 2, 'IdDocente' => 2],
        ]);
        DB::table('departamento')->insert([
            ['IdDepartamento' => 1, 'nombreDepartamento' => 'Departamento 1'],
            ['IdDepartamento' => 2, 'nombreDepartamento' => 'Departamento 2'],
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
                'IdTipoDocumento' =>1 ,
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

        // Realizar la solicitud
        $response = $this->post(route('filtrar.consulta'), [
            'TipoDocumento' => ['IdTipoDocumento' => 1, 'nombreTipoDoc' => 'Tipo 1'],
            'PeriodoEscolar' => ['IdPeriodoEscolar' => 1, 'nombre_corto' => '2023-2024'],
            'Departamento' => ['IdDepartamento' => 1, 'nombreDepartamento' => 'Departamento 1'],
            'Region' => 'Interno',
            'Estatus' => 'En Proceso'
        ]);

        // Verificar la respuesta
        $response->assertStatus(200);
    }

    public function testFiltrarConsultaConRegistros_Secretaria()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // Crear datos necesarios para la prueba
        DB::table('tipo_documento')->insert([
            ['IdTipoDocumento' => 1, 'nombreTipoDoc' => 'Tipo 1'],
            ['IdTipoDocumento' => 2, 'nombreTipoDoc' => 'Tipo 2'],
        ]);

        DB::table('personal')->insert([
            ['IdPersonal' => 1, 'Nombre' => 'Usuario Prueba 1', 'Apellidos' => 'Apellidos 1', 'Sexo' => 'Hombre'],
            ['IdPersonal' => 2, 'Nombre' => 'Usuario Prueba 2', 'Apellidos' => 'Apellidos 2', 'Sexo' => 'Mujer'],
        ]);

        DB::table('users')->insert([
            'id' => 1,
            'email' => 'prueba@example.com',
            'password' => bcrypt('password'),
            'IdPersonal' => 1
        ]);
        DB::table('secretaria')->insert(
            ['IdSecretaria'=> 100,'IdPersonal' =>1],
        );
        DB::table('docente')->insert([
            ['IdDocente' => 1, 'IdPersonal' => 1, 'GradoAcademico' => 'Licenciatura'],
            ['IdDocente' => 2, 'IdPersonal' => 2, 'GradoAcademico' => 'Licenciatura'],
        ]);

        DB::table('expediente')->insert([
            ['IdExpediente' => 1, 'IdDocente' => 1],
            ['IdExpediente' => 2, 'IdDocente' => 2],
        ]);
        DB::table('departamento')->insert([
            ['IdDepartamento' => 1, 'nombreDepartamento' => 'Departamento 1'],
            ['IdDepartamento' => 2, 'nombreDepartamento' => 'Departamento 2'],
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
                'IdTipoDocumento' =>1 ,
                'IdPeriodoEscolar' => 1,
                'IdExpediente' => 1,
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

        // Realizar la solicitud
        $response = $this->post(route('filtrar.consulta'), [
            'TipoDocumento' => null,
            'PeriodoEscolar' => ['IdPeriodoEscolar' => 1, 'nombre_corto' => '2023-2024'],
            'Departamento' => null,
            'Region' => 'Interno',
            'Estatus' => 'En proceso'
        ]);

        // Verificar la respuesta
        $response->assertStatus(200);
    }
    public function testFiltrarConsultaConRegistros_Ext_Entregados()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // Crear datos necesarios para la prueba
        DB::table('tipo_documento')->insert([
            ['IdTipoDocumento' => 1, 'nombreTipoDoc' => 'Tipo 1'],
            ['IdTipoDocumento' => 2, 'nombreTipoDoc' => 'Tipo 2'],
        ]);

        DB::table('personal')->insert([
            ['IdPersonal' => 1, 'Nombre' => 'Usuario Prueba 1', 'Apellidos' => 'Apellidos 1', 'Sexo' => 'Hombre'],
            ['IdPersonal' => 2, 'Nombre' => 'Usuario Prueba 2', 'Apellidos' => 'Apellidos 2', 'Sexo' => 'Mujer'],
        ]);

        DB::table('users')->insert([
            'id' => 1,
            'email' => 'prueba@example.com',
            'password' => bcrypt('password'),
            'IdPersonal' => 1
        ]);
        DB::table('secretaria')->insert(
            ['IdSecretaria'=> 100,'IdPersonal' =>1],
        );
        DB::table('docente')->insert([
            ['IdDocente' => 1, 'IdPersonal' => 1, 'GradoAcademico' => 'Licenciatura'],
            ['IdDocente' => 2, 'IdPersonal' => 2, 'GradoAcademico' => 'Licenciatura'],
        ]);

        DB::table('expediente')->insert([
            ['IdExpediente' => 1, 'IdDocente' => 1],
            ['IdExpediente' => 2, 'IdDocente' => 2],
        ]);
        DB::table('departamento')->insert([
            ['IdDepartamento' => 1, 'nombreDepartamento' => 'Departamento 1'],
            ['IdDepartamento' => 2, 'nombreDepartamento' => 'Departamento 2'],
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
                'IdTipoDocumento' =>1 ,
                'IdPeriodoEscolar' => 1,
                'IdExpediente' => 1,
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

        // Realizar la solicitud
        $response = $this->post(route('filtrar.consulta'), [
            'TipoDocumento' => null,
            'PeriodoEscolar' => ['IdPeriodoEscolar' => 1, 'nombre_corto' => '2023-2024'],
            'Departamento' => null,
            'Region' => 'Externo',
            'Estatus' => 'Entregado'
        ]);

        // Verificar la respuesta
        $response->assertStatus(200);
    }
}
