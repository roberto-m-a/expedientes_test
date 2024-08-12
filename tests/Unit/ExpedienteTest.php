<?php

namespace Tests\Unit;

use App\Http\Controllers\expedienteController;
use App\Models\Administrador;
use App\Models\Departamento;
use App\Models\Docente;
use App\Models\expediente;
use App\Models\PeriodoEscolar;
use App\Models\Personal;
use App\Models\Secretaria;
use App\Models\TipoDocumento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response as InertiaResponse;

class ExpedienteTest extends TestCase
{
    use RefreshDatabase;
    public function test_expediente_tiene_relacion_con_un_docente(){
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
        $docente = Docente::create([
            'IdPersonal' => $personal->IdPersonal,
            'GradoAcademico' => 'Licenciatura', // Agrega los campos necesarios
        ]);

        $expediente = expediente::create([
            'IdDocente' => $docente->IdDocente,
            'numDocumentos' => 0, // Agrega los campos necesarios
        ]);

        $this->assertInstanceOf(Docente::class, $expediente->docente);
        $this->assertEquals($expediente->IdDocente, $docente->IdDocente);
    }

    public function test_renderiza_la_vista_de_expedientes_secretaria(){
        $departamento = Departamento::create([
            'IdDepartamento' => 1234,
            'nombreDepartamento' => 'Sistemas',
        ]);
        //Agregamos un docente con su expediente
        $personalDoc1 = Personal::create([
            'Nombre' => 'Virginia',
            'Apellidos' => 'Ortiz',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $docente1 = Docente::create([
            'IdPersonal' => $personalDoc1->IdPersonal,
            'GradoAcademico' => 'Licenciatura',
        ]);
        $expediente1 = expediente::create([
            'IdDocente' => $docente1->IdDocente,
        ]);
        //Agregamos al personal de Secretaria
        $personalSecretaria = Personal::create([
            'Nombre' => 'Virginia',
            'Apellidos' => 'Ortiz',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Mujer',
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
        // Crear una instancia del controlador
        $controller = new expedienteController();

        // Simular una solicitud
        $request = Request::create('/Expedientes', 'GET');
        $response = $controller->index($request);

        // Verificar los datos devueltos
        $this->assertInstanceOf(InertiaResponse::class, $response);

        // Verificar contenido de la respuesta para el rol de Secretaría
        $responseContent = $response->toResponse($request)->getContent();
        $this->assertStringContainsString('Dashboard_secre_expedientes', $responseContent);
    }

    public function test_renderiza_la_vista_de_expedientes_admin(){
        $departamento = Departamento::create([
            'IdDepartamento' => 1234,
            'nombreDepartamento' => 'Sistemas',
        ]);
        //Agregamos un docente con su expediente
        $personalDoc1 = Personal::create([
            'Nombre' => 'Virginia',
            'Apellidos' => 'Ortiz',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $docente1 = Docente::create([
            'IdPersonal' => $personalDoc1->IdPersonal,
            'GradoAcademico' => 'Licenciatura',
        ]);
        $expediente1 = expediente::create([
            'IdDocente' => $docente1->IdDocente,
        ]);
        //Agregamos al personal de Secretaria
        $personalSecretaria = Personal::create([
            'Nombre' => 'Virginia',
            'Apellidos' => 'Ortiz',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Mujer',
        ]);
        $administrador = Administrador::create([
            'IdPersonal' => $personalSecretaria->IdPersonal,
        ]);
        $user = User::create([
            'id' => 7777,
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personalSecretaria->IdPersonal,
        ]);
        Auth::login($user);//logueamos al usuario de la secretaria
        // Crear una instancia del controlador
        $controller = new expedienteController();

        // Simular una solicitud
        $request = Request::create('/Expedientes', 'GET');
        $response = $controller->index($request);

        // Verificar los datos devueltos
        $this->assertInstanceOf(InertiaResponse::class, $response);

        // Verificar contenido de la respuesta para el rol de Secretaría
        $responseContent = $response->toResponse($request)->getContent();
        $this->assertStringContainsString('Dashboard_admin_expedientes', $responseContent);
    }

    public function test_renderiza_la_vista_del_expediente_del_docente(){
        // Crear tipos de documentos
        $tipo_documentos = [
            ['IdTipoDocumento' => 1, 'nombreTipoDoc' => 'Tipo A'],
            ['IdTipoDocumento' => 2, 'nombreTipoDoc' => 'Tipo B'],
            ['IdTipoDocumento' => 3, 'nombreTipoDoc' => 'Tipo C'],
        ];

        foreach ($tipo_documentos as $tipo_documento) {
            TipoDocumento::create($tipo_documento);
        }
        $periodoEscolar = PeriodoEscolar::create([
            'fechaInicio' => '2024-01-01',
            'fechaTermino' =>  '2024-06-06',
            'nombre_corto' => 'ENE-JUN 2024',
        ]);
        $departamento = Departamento::create([
            'IdDepartamento' => 1234,
            'nombreDepartamento' => 'Sistemas',
        ]);
        //Agregamos un docente con su expediente
        $personalDoc1 = Personal::create([
            'Nombre' => 'Virginia',
            'Apellidos' => 'Ortiz',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $docente1 = Docente::create([
            'IdPersonal' => $personalDoc1->IdPersonal,
            'GradoAcademico' => 'Licenciatura',
        ]);
        $expediente1 = expediente::create([
            'IdDocente' => $docente1->IdDocente,
        ]);
        
        $user = User::create([
            'id' => 7777,
            'email' => 'test@itoaxaca.edu.mx',
            'password' => bcrypt('password'),
            'IdPersonal' => $personalDoc1->IdPersonal,
        ]);
        Auth::login($user);//logueamos al usuario de la secretaria
        // Crear una instancia del controlador
        $controller = new expedienteController();

        // Simular una solicitud
        $request = Request::create('/Expedientes', 'GET');
        $response = $controller->index($request);

        // Verificar los datos devueltos
        $this->assertInstanceOf(InertiaResponse::class, $response);

        // Verificar contenido de la respuesta para el rol de Secretaría
        $responseContent = $response->toResponse($request)->getContent();
        $this->assertStringContainsString('Dashboard_miExpediente', $responseContent);
    }

    public function test_Renderiza_expediente_especifico_correctamente_admin(){
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

        $controller = new expedienteController();

        // Simular una solicitud
        $request = Request::create('/expediente/2', 'GET');
        $response = $controller->expedienteEspecifico(2);

        // Verificar los datos devueltos
        $this->assertInstanceOf(InertiaResponse::class, $response);

        // Verificar contenido de la respuesta para el rol de Secretaría
        $responseContent = $response->toResponse($request)->getContent();
        $this->assertStringContainsString('Dashboard_admin_expedienteEspecifico', $responseContent);
    }

    public function test_Renderiza_expediente_especifico_correctamente_secretaria(){
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

        $controller = new expedienteController();

        // Simular una solicitud
        $request = Request::create('/expediente/2', 'GET');
        $response = $controller->expedienteEspecifico(2);

        // Verificar los datos devueltos
        $this->assertInstanceOf(InertiaResponse::class, $response);

        // Verificar contenido de la respuesta para el rol de Secretaría
        $responseContent = $response->toResponse($request)->getContent();
        $this->assertStringContainsString('Dashboard_secre_expedienteEspecifico', $responseContent);
    }
}
