<?php

namespace Tests\Unit;

use App\Models\Departamento;
use App\Models\Personal;
use App\Models\Secretaria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecretariaTest extends TestCase
{
    use RefreshDatabase;
    function test_secretaria_tiene_relacion_con_un_personal(){
        $departamento = Departamento::create([
            'nombreDepartamento' => 'Sistemas',
        ]);
        $personal = Personal::create([
            'Nombre' => 'Roberto',
            'Apellidos' => 'Manuel',
            'IdDepartamento' => $departamento->IdDepartamento,
            'Sexo' => 'Hombre',
        ]);
        $secretaria = Secretaria::create([
            'IdPersonal' => $personal->IdPersonal,
        ]); 

        $this->assertInstanceOf(Personal::class, $secretaria->personal);
        $this->assertEquals($personal->IdPersonal, $secretaria->personal->IdPersonal);
    }
}
