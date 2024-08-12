<?php

namespace Tests\Unit;

use App\Models\Administrador;
use App\Models\Departamento;
use App\Models\Personal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministradorTest extends TestCase
{
    use RefreshDatabase;
    public function test_administrador_tiene_relacion_con_un_personal()
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
        $administrador = Administrador::create([
            'IdPersonal' => $personal->IdPersonal,
        ]); 

        $this->assertInstanceOf(Personal::class, $administrador->personal);
        $this->assertEquals($personal->IdPersonal, $administrador->personal->IdPersonal);
    }
    
}
