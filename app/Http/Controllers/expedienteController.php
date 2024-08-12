<?php

namespace App\Http\Controllers;

use App\Models\Administrador;
use App\Models\Departamento;
use App\Models\Docente;
use App\Models\expediente;
use App\Models\PeriodoEscolar;
use App\Models\Personal;
use App\Models\TipoDocumento;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class expedienteController extends Controller
{
    //
    public function index()
    {
        $user = User::find(Auth::user()->id);
        $personal = Personal::where('IdPersonal', Auth::user()->IdPersonal)->first();
        if (Docente::where('IdPersonal', Auth::user()->IdPersonal)->first() !== null) {
            $docente = Docente::where('IdPersonal', Auth::user()->IdPersonal)->first();
            $expediente = expediente::where('IdDocente', $docente->IdDocente)->first();
            $tipo_documentos = TipoDocumento::all();
            $departamentos = Departamento::all();
            $periodos_escolares = PeriodoEscolar::all();
            $periodosEscolaresM = $periodos_escolares->map(function ($periodo) {
                $periodo->generalInfo = $periodo->nombre_corto . ' (' . $periodo->fechaInicio . '-' . $periodo->fechaTermino . ')';
                return $periodo;
            });
            $documentosDocente = DB::table('documento')
                ->where('IdExpediente', '=', $expediente->IdExpediente)
                ->join('tipo_documento', 'tipo_documento.IdTipoDocumento', '=', 'documento.IdTipoDocumento')
                ->join('periodo_escolar', 'periodo_escolar.IdPeriodoEscolar', '=', 'documento.IdPeriodoEscolar')
                ->leftJoin('departamento', 'departamento.IdDepartamento', '=', 'documento.IdDepartamento')
                ->select('documento.*', 'tipo_documento.nombreTipoDoc', 'periodo_escolar.nombre_corto', 'departamento.nombreDepartamento')
                ->get();
            //dd($documentosDocente);
            return Inertia::render('Dashboard_miExpediente', ['user' => $user, 'personal' => $personal, 'documentosDocente' => $documentosDocente, 'expediente' => $expediente, 'periodos_escolares',  'tipo_documentos' => $tipo_documentos, 'departamentos' => $departamentos, 'periodos_escolares' => $periodosEscolaresM]);
        } else {
            $expedientes = DB::table('expediente')
                ->join('docente', 'docente.IdDocente', '=', 'expediente.IdDocente')
                ->join('personal', 'personal.IdPersonal', '=', 'docente.IdPersonal')
                ->join('departamento', 'departamento.IdDepartamento', '=', 'personal.IdDepartamento')
                ->select('personal.*', 'expediente.*', 'departamento.*')
                ->get();
            //dd($expedientes);
            if (Administrador::where('IdPersonal', $personal->IdPersonal)->first() !== null) {
                return Inertia::render('Dashboard_admin_expedientes', ['user' => $user, 'personal' => $personal, 'expedientes' => $expedientes]);
            } else {
                return Inertia::render('Dashboard_secre_expedientes', ['user' => $user, 'personal' => $personal, 'expedientes' => $expedientes]);
            }
        }
    }
    public function expedienteEspecifico($idExpediente)
    {
        $expediente = expediente::where('IdExpediente', $idExpediente)->first();
        $docente = Docente::where('IdDocente', $expediente->IdDocente)->first();
        $personalDocente = Personal::where('IdPersonal', $docente->IdPersonal)->first();

        $user = User::find(Auth::user()->id);
        $personal = Personal::where('IdPersonal', Auth::user()->IdPersonal)->first();

        $tipo_documentos = TipoDocumento::all();
        $departamentos = Departamento::all();
        $periodos_escolares = PeriodoEscolar::all();
        $periodosEscolaresM = $periodos_escolares->map(function ($periodo) {
            $periodo->generalInfo = $periodo->nombre_corto . ' (' . $periodo->fechaInicio . '-' . $periodo->fechaTermino . ')';
            return $periodo;
        });

        $documentosDocente = DB::table('documento')
            ->where('IdExpediente', '=', $expediente->IdExpediente)
            ->join('tipo_documento', 'tipo_documento.IdTipoDocumento', '=', 'documento.IdTipoDocumento')
            ->join('periodo_escolar', 'periodo_escolar.IdPeriodoEscolar', '=', 'documento.IdPeriodoEscolar')
            ->leftJoin('departamento', 'departamento.IdDepartamento', '=', 'documento.IdDepartamento')
            ->select('documento.*', 'tipo_documento.nombreTipoDoc', 'periodo_escolar.nombre_corto', 'departamento.nombreDepartamento')
            ->get();

        if (Administrador::where('IdPersonal', $personal->IdPersonal)->first() !== null) {
            return Inertia::render('Dashboard_admin_expedienteEspecifico', ['user' => $user, 'personal' => $personal, 'personalDocente' => $personalDocente, 'documentosDocente' => $documentosDocente, 'expediente' => $expediente, 'tipo_documentos' => $tipo_documentos, 'departamentos' => $departamentos, 'periodos_escolares' => $periodosEscolaresM]);
        } else {
            return Inertia::render('Dashboard_secre_expedienteEspecifico', ['user' => $user, 'personal' => $personal, 'personalDocente' => $personalDocente, 'documentosDocente' => $documentosDocente, 'expediente' => $expediente, 'tipo_documentos' => $tipo_documentos, 'departamentos' => $departamentos, 'periodos_escolares' => $periodosEscolaresM]);
        }
    }
}
