<?php

namespace App\Http\Controllers;

use App\Models\Administrador;
use App\Models\Departamento;
use App\Models\Docente;
use App\Models\expediente;
use App\Models\PeriodoEscolar;
use App\Models\Personal;
use App\Models\Secretaria;
use App\Models\TipoDocumento;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class DashboardController extends Controller
{
    //
    public function index()
    {
        $user = User::find(Auth::user()->id);
        $personal = Personal::where('IdPersonal', Auth::user()->IdPersonal)->first();
        $departamentos = Departamento::all();
        $verificarContraseña = false;
        if ($user->password == null) {
            $verificarContraseña = true;
        }

        if (Docente::where('IdPersonal', $personal->IdPersonal)->first() != null) {
            $docente = Docente::where('IdPersonal', $personal->IdPersonal)->first();
            $expediente = expediente::where('IdDocente', $docente->IdDocente)->first();
            $tipos_documentos = DB::table('tipo_documento')->get();

            $combinaciones = [];
            foreach ($tipos_documentos as $tipo_documento) {
                $combinaciones[] = [
                    'IdTipoDocumento' => $tipo_documento->IdTipoDocumento,
                    'nombreTipoDoc' => $tipo_documento->nombreTipoDoc,
                ];
            }
            //dd($combinaciones);
            $documentos_data = collect($combinaciones)->map(function ($combinacion) use ($expediente) {
                $cantidad = DB::table('documento')
                    ->where('documento.IdExpediente', $expediente->IdExpediente)
                    ->where('documento.IdTipoDocumento', $combinacion['IdTipoDocumento'])
                    ->count();

                return [
                    'IdTipoDocumento' => $combinacion['IdTipoDocumento'],
                    'nombreTipoDoc' => $combinacion['nombreTipoDoc'],
                    'cantidad' => $cantidad
                ];
            });
            $interpretacion = 'Cantidad de documentos en mi expediente';
            return Inertia::render('Dashboard', ['verificarContraseña' => $verificarContraseña, 'user' => $user, 'personal' => $personal, 'departamentos' => $departamentos, 'documentos_data' => $documentos_data, 'interpretacion' => $interpretacion]);
        } else {
            $tipo_documentos = TipoDocumento::all();
            $periodos_escolares = PeriodoEscolar::all();
            $periodos_escolaresM = $periodos_escolares->map(function ($periodo) {
                $periodo->generalInfo = $periodo->nombre_corto . ' (' . $periodo->fechaInicio . '-' . $periodo->fechaTermino . ')';
                return $periodo;
            });
            // Crear una colección con los sexos
            $sexos = ['Hombre', 'Mujer'];

            // Obtener los tipos de documentos
            $tipos_documentos = DB::table('tipo_documento')->get();

            $combinaciones = [];

            foreach ($tipos_documentos as $tipo_documento) {
                foreach ($sexos as $sexo) {
                    $combinaciones[] = [
                        'IdTipoDocumento' => $tipo_documento->IdTipoDocumento,
                        'nombreTipoDoc' => $tipo_documento->nombreTipoDoc,
                        'Sexo' => $sexo
                    ];
                }
            }

            $documentos_data = collect($combinaciones)->map(function ($combinacion) {
                $cantidad = DB::table('documento')
                    ->join('expediente', 'expediente.IdExpediente', '=', 'documento.IdExpediente')
                    ->join('docente', 'docente.IdDocente', '=', 'expediente.IdDocente')
                    ->join('personal', 'personal.IdPersonal', '=', 'docente.IdPersonal')
                    ->where('documento.IdTipoDocumento', $combinacion['IdTipoDocumento'])
                    ->where('personal.Sexo', $combinacion['Sexo'])
                    ->count();

                return [
                    'IdTipoDocumento' => $combinacion['IdTipoDocumento'],
                    'nombreTipoDoc' => $combinacion['nombreTipoDoc'],
                    'Sexo' => $combinacion['Sexo'],
                    'cantidad' => $cantidad
                ];
            });
            $interpretacion = 'Cantidad de documentos por cada tipo de documento';
            //dd($documentos_data);
            $dataHombres = [];
            $dataMujeres = [];
            foreach ($documentos_data as $documento) {
                if ($documento['Sexo'] == 'Hombre') {
                    array_push($dataHombres, $documento['cantidad']);
                } else {
                    array_push($dataMujeres, $documento['cantidad']);
                }
            }
            $labelsTipoDoc = [];
            foreach ($tipo_documentos as $tipoDoc) {
                array_push($labelsTipoDoc, $tipoDoc['nombreTipoDoc']);
            }
            if (Administrador::where('IdPersonal', $personal->IdPersonal)->first() !== null) {
                return Inertia::render('Dashboard_admin', ['verificarContraseña' => $verificarContraseña, 'user' => $user, 'personal' => $personal, 'tipo_documentos' => $tipo_documentos, 'departamentos' => $departamentos, 'periodos_escolares' => $periodos_escolaresM, 'documentos_data' => $documentos_data, 'interpretacion' => $interpretacion, 'dataHombres' => $dataHombres, 'dataMujeres' => $dataMujeres, 'labelsTipoDoc' => $labelsTipoDoc]);
            }
            if (Secretaria::where('IdPersonal', $personal->IdPersonal)->first() !== null) {
                return Inertia::render('Dashboard_secretaria', ['verificarContraseña' => $verificarContraseña, 'user' => $user, 'personal' => $personal, 'tipo_documentos' => $tipo_documentos, 'departamentos' => $departamentos, 'periodos_escolares' => $periodos_escolaresM, 'documentos_data' => $documentos_data, 'interpretacion' => $interpretacion, 'dataHombres' => $dataHombres, 'dataMujeres' => $dataMujeres, 'labelsTipoDoc' => $labelsTipoDoc]);
            }
        }
    }

    public function filtrarConsulta(Request $request)
    {
        // Crear una colección con los sexos
        $sexos = ['Hombre', 'Mujer'];

        // Obtener los tipos de documentos
        $tipos_documentos = DB::table('tipo_documento')->get();

        $combinaciones = [];

        foreach ($tipos_documentos as $tipo_documento) {
            foreach ($sexos as $sexo) {
                $combinaciones[] = [
                    'IdTipoDocumento' => $tipo_documento->IdTipoDocumento,
                    'nombreTipoDoc' => $tipo_documento->nombreTipoDoc,
                    'Sexo' => $sexo
                ];
            }
        }

        $documentos_data = collect($combinaciones)->map(function ($combinacion) use ($request) {
            $documentos_datos = DB::table('documento')
                ->join('expediente', 'expediente.IdExpediente', '=', 'documento.IdExpediente')
                ->join('docente', 'docente.IdDocente', '=', 'expediente.IdDocente')
                ->join('personal', 'personal.IdPersonal', '=', 'docente.IdPersonal')
                ->where('documento.IdTipoDocumento', $combinacion['IdTipoDocumento'])
                ->where('personal.Sexo', $combinacion['Sexo']);

            // Agregar filtros condicionales
            if ($request->TipoDocumento != '' || $request->TipoDocumento != null) {
                $documentos_datos->where('IdTipoDocumento', $request->TipoDocumento['IdTipoDocumento']);
            }
            if ($request->PeriodoEscolar != '' || $request->PeriodoEscolar != null) {
                $documentos_datos->where('IdPeriodoEscolar', $request->PeriodoEscolar['IdPeriodoEscolar']);
            }
            if ($request->Departamento != '' || $request->Departamento != null) {
                $documentos_datos->where('documento.IdDepartamento', $request->Departamento['IdDepartamento']);
            } else {
                if ($request->Region != "Todos") {
                    $documentos_datos->where('Region', $request->Region);
                }
            }
            if ($request->Estatus != "Todos") {
                $documentos_datos->where('Estatus', $request->Estatus);
            }
            // Obtener la cantidad de documentos que coinciden con los criterios
            $cantidad = $documentos_datos->count();

            return [
                'IdTipoDocumento' => $combinacion['IdTipoDocumento'],
                'nombreTipoDoc' => $combinacion['nombreTipoDoc'],
                'Sexo' => $combinacion['Sexo'],
                'cantidad' => $cantidad
            ];
        });
        $registros_vacios = $documentos_data->every(function ($item) {
            return $item['cantidad'] === 0;
        });

        if ($registros_vacios) {
            return Redirect::route('dashboard')->with('sinRegistros', 'Al parecer no hay registros con los parámetros ingresados');
        }
        //dd($documentos_data);
        $interpretacion = 'Cantidad de documentos';

        if ($request->TipoDocumento != '' || $request->TipoDocumento != null) {
            $interpretacion = $interpretacion . ' de ' . $request->TipoDocumento['nombreTipoDoc'];
        }
        if ($request->Estatus != "Todos") {
            if ($request->Estatus == "Entregado")
                $interpretacion = $interpretacion . ' Entregados';
            else
                $interpretacion = $interpretacion . ' En proceso';
        }
        if ($request->PeriodoEscolar != '' || $request->PeriodoEscolar != null) {

            $interpretacion = $interpretacion . ' en ' . $request->PeriodoEscolar['nombre_corto'];
        }
        if ($request->Departamento != '' || $request->Departamento != null) {
            $interpretacion = $interpretacion . ' del departamento de ' . $request->Departamento['nombreDepartamento'];
        } else {
            if ($request->Region != "Todos") {
                if ($request->Region == 'Interno')
                    $interpretacion = $interpretacion . ' (internos)';
                else
                    $interpretacion = $interpretacion . ' (externos)';
            }
        }
        $dataHombres = [];
        $dataMujeres = [];
        if ($request->TipoDocumento != '' || $request->TipoDocumento != null) {
            foreach ($documentos_data as $documento) {
                if ($documento['nombreTipoDoc'] == $request->TipoDocumento['nombreTipoDoc'])
                    if ($documento['Sexo'] == 'Hombre') {
                        array_push($dataHombres, $documento['cantidad']);
                    } else {
                        array_push($dataMujeres, $documento['cantidad']);
                    }
            }
        } else {
            foreach ($documentos_data as $documento) {

                if ($documento['Sexo'] == 'Hombre') {
                    array_push($dataHombres, $documento['cantidad']);
                } else {
                    array_push($dataMujeres, $documento['cantidad']);
                }
            }
        }
        $tipo_documentos = TipoDocumento::all();
        $labelsTipoDoc = [];
        if ($request->TipoDocumento != '' || $request->TipoDocumento != null) {
            array_push($labelsTipoDoc, $request->TipoDocumento['nombreTipoDoc']);
        } else {
            foreach ($tipo_documentos as $tipoDoc) {
                array_push($labelsTipoDoc, $tipoDoc['nombreTipoDoc']);
            }
        }
        $departamentos = Departamento::all();
        $periodos_escolares = PeriodoEscolar::all();
        $periodos_escolaresM = $periodos_escolares->map(function ($periodo) {
            $periodo->generalInfo = $periodo->nombre_corto . ' (' . $periodo->fechaInicio . '-' . $periodo->fechaTermino . ')';
            return $periodo;
        });
        $user = User::find(Auth::user()->id);
        $personal = Personal::where('IdPersonal', Auth::user()->IdPersonal)->first();
        if (Administrador::where('IdPersonal', $personal->IdPersonal)->first() !== null) {
            return Inertia::render('Dashboard_admin', ['user' => $user, 'personal' => $personal, 'tipo_documentos' => $tipo_documentos, 'departamentos' => $departamentos, 'periodos_escolares' => $periodos_escolaresM, 'interpretacion' => $interpretacion, 'dataHombres' => $dataHombres, 'dataMujeres' => $dataMujeres, 'labelsTipoDoc' => $labelsTipoDoc]);
        }
        if (Secretaria::where('IdPersonal', $personal->IdPersonal)->first() !== null) {
            return Inertia::render('Dashboard_secretaria', ['user' => $user, 'personal' => $personal, 'tipo_documentos' => $tipo_documentos, 'departamentos' => $departamentos, 'periodos_escolares' => $periodos_escolaresM, 'interpretacion' => $interpretacion, 'dataHombres' => $dataHombres, 'dataMujeres' => $dataMujeres, 'labelsTipoDoc' => $labelsTipoDoc]);
        }
    }
}
