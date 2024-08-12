<?php

namespace Tests\Unit;

use App\Models\Departamento;
use App\Models\Docente;
use App\Models\expediente;
use App\Models\Personal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocenteTest extends TestCase
{
    use RefreshDatabase;
    public function test_docente_tiene_relacion_con_personal(){
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $docente = Docente::create([
            'IdPersonal' => $personal->IdPersonal,
            'GradoAcademico' => 'Licenciatura',
        ]);

        $this->assertInstanceOf(Personal::class, $docente->personal);
        $this->assertEquals($personal->IdPersonal, $docente->personal->IdPersonal);
    }

    public function test_docente_tiene_relacion_con_expediente(){
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $docente = Docente::create([
            'IdPersonal' => $personal->IdPersonal,
            'GradoAcademico' => 'Licenciatura',
        ]);

        $expediente = expediente::create([
            'IdDocente' => $docente->IdDocente,
        ]);

        $this->assertInstanceOf(expediente::class, $docente->expediente);
        $this->assertEquals($expediente->IdDocente, $docente->IdDocente);
    }
}
