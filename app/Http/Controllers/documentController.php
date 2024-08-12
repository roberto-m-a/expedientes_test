<?php

namespace App\Http\Controllers;

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
use DateTime;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class documentController extends Controller
{
    //
    public function index()
    {
        $user = User::find(Auth::user()->id);
        $personal = Personal::where('IdPersonal', Auth::user()->IdPersonal)->first();
        $departamentos = Departamento::all();
        $periodosEscolares = PeriodoEscolar::all();
        $tiposDocumentos = TipoDocumento::all();
        $periodosEscolaresM = $periodosEscolares->map(function ($periodo) {
            $periodo->generalInfo = $periodo->nombre_corto . ' (' . $periodo->fechaInicio . '-' . $periodo->fechaTermino . ')';
            return $periodo;
        });
        if (Docente::where('IdPersonal', Auth::user()->IdPersonal)->first() !== null) {
            $docente = Docente::where('IdPersonal', Auth::user()->IdPersonal)->first();
            $expediente = expediente::where('IdDocente', $docente->IdDocente)->first();
            return Inertia::render('Dashboard_subirDocumento', ['user' => $user, 'personal' => $personal, 'departamentos' => $departamentos, 'periodosEscolares' => $periodosEscolaresM, 'expediente' => $expediente, 'tiposDocumentos' => $tiposDocumentos]);
        } else {
            $expediente_data = DB::table('expediente')
                ->join('docente', 'docente.IdDocente', '=', 'expediente.IdDocente')
                ->join('personal', 'personal.IdPersonal', '=', 'docente.IdPersonal')
                ->join('departamento', 'departamento.IdDepartamento', '=', 'personal.IdDepartamento')
                ->select('personal.*', 'expediente.IdExpediente', 'departamento.nombreDepartamento')
                ->get();

            $expediente_dataM = $expediente_data->map(function ($expediente) {
                $expediente->generalInfo = $expediente->Nombre . ' ' . $expediente->Apellidos . ' - ' . $expediente->nombreDepartamento;
                return $expediente;
            });
            //dd($expediente_data);
            //dd($expediente_dataM);
            if (Secretaria::where('IdPersonal', Auth::user()->IdPersonal)->first() !== null) {
                return Inertia::render('Dashboard_secre_nuevoDocumento', ['user' => $user, 'personal' => $personal, 'departamentos' => $departamentos, 'expediente_data' => $expediente_dataM, 'periodosEscolares' => $periodosEscolaresM, 'tiposDocumentos' => $tiposDocumentos]);
            }
            return Inertia::render('Dashboard_admin_nuevoDocumento', ['user' => $user, 'personal' => $personal, 'departamentos' => $departamentos, 'expediente_data' => $expediente_dataM, 'periodosEscolares' => $periodosEscolaresM, 'tiposDocumentos' => $tiposDocumentos]);
        }
    }

    public function documentsIndex()
    {
        $user = User::find(Auth::user()->id);
        $personal = Personal::where('IdPersonal', Auth::user()->IdPersonal)->first();
        $tipo_documentos = TipoDocumento::all();
        $departamentos = Departamento::all();
        $periodos_escolares = PeriodoEscolar::all();
        $periodosEscolaresM = $periodos_escolares->map(function ($periodo) {
            $periodo->generalInfo = $periodo->nombre_corto . ' (' . $periodo->fechaInicio . '-' . $periodo->fechaTermino . ')';
            return $periodo;
        });
        $expediente_data = DB::table('expediente')
            ->join('docente', 'docente.IdDocente', '=', 'expediente.IdDocente')
            ->join('personal', 'personal.IdPersonal', '=', 'docente.IdPersonal')
            ->join('departamento', 'departamento.IdDepartamento', '=', 'personal.IdDepartamento')
            ->select('personal.*', 'expediente.IdExpediente', 'departamento.nombreDepartamento')
            ->get();

        $expediente_dataM = $expediente_data->map(function ($expediente) {
            $expediente->generalInfo = $expediente->Nombre . ' ' . $expediente->Apellidos . ' - ' . $expediente->nombreDepartamento;
            return $expediente;
        });
        $documentos = DB::table('documento')
            ->join('tipo_documento', 'tipo_documento.IdTipoDocumento', '=', 'documento.IdTipoDocumento')
            ->join('periodo_escolar', 'periodo_escolar.IdPeriodoEscolar', '=', 'documento.IdPeriodoEscolar')
            ->join('expediente', 'expediente.IdExpediente', '=', 'documento.IdExpediente')
            ->join('docente', 'docente.IdDocente', '=', 'expediente.IdDocente')
            ->join('personal', 'personal.IdPersonal', '=', 'docente.IdPersonal')
            ->leftJoin('departamento', 'departamento.IdDepartamento', '=', 'documento.IdDepartamento')
            ->select('documento.*', 'tipo_documento.nombreTipoDoc', 'periodo_escolar.nombre_corto', 'departamento.nombreDepartamento', 'personal.Nombre', 'personal.Apellidos')
            ->get();
        if (Administrador::where('IdPersonal', $personal->IdPersonal)->first() !== null) {
            return Inertia::render('Dashboard_admin_documentos', ['user' => $user, 'personal' => $personal, 'documentos' => $documentos, 'tipo_documentos' => $tipo_documentos, 'departamentos' => $departamentos, 'periodos_escolares' => $periodosEscolaresM, 'expedientes' => $expediente_dataM]);
        }
        //dd(Secretaria::where('IdPersonal',$personal->IdPersonal));
        if (Secretaria::where('IdPersonal', $personal->IdPersonal)->first() !== null) {
            return Inertia::render('Dashboard_secre_documentos', ['user' => $user, 'personal' => $personal, 'documentos' => $documentos, 'tipo_documentos' => $tipo_documentos, 'departamentos' => $departamentos, 'periodos_escolares' => $periodosEscolaresM, 'expedientes' => $expediente_dataM]);
        }
    }

    public function nuevoDocumento(Request $request)
    {
        $request->validate([
            'Expediente' => 'required',
            'TipoDocumento' => 'required',
            'Titulo' => 'required',
            'FechaExpedicion' => 'required',
            'Region' => 'required',
            'PeriodoEscolar' => 'required',
            'Archivo' => 'required|file|max:5120',
        ]);
        if (strpos($request->Archivo->getClientOriginalName(), '.pdf') == false) {
            throw ValidationException::withMessages([
                'Archivo' => 'Debes de ingresar un archivo PDF',
            ]);
        }
        if ($request->Region == 'Interno') {
            $request->validate([
                'Departamento' => 'required',
                'Estatus' => 'required',
            ]);
        } else {
            $request->validate([
                'Dependencia' => 'required',
            ]);
        }
        if ($request->Estatus == 'Entregado') {
            $request->validate([
                'FechaEntrega' => 'required',
            ]);
            if ($request->FechaExpedicion > $request->FechaEntrega) {
                throw ValidationException::withMessages([
                    'FechaExpedicion' => 'Las fecha de expedición no puede ser despues de la fecha de entrega',
                    'FechaEntrega' => 'Las fecha de entrega no puede ser antes de la fecha de expedición',
                ]);
            }
        }

        $fecha = new DateTime(); // Obtener la fecha actual como objeto DateTime
        $archivo = $request->Archivo;
        $nombreArchivo = date_format($fecha, 'Y-m-d_H_i_s') . '_' . $archivo->getClientOriginalName();
        $path = Storage::putFileAs('public/documents', $archivo, $nombreArchivo);
        if (Storage::exists($path)) {
            $document = document::create([
                'Titulo' => $request->Titulo,
                'fechaExpedicion' => $request->FechaExpedicion,
                'fechaEntrega' => ($request->Estatus == 'Entregado') ? $request->FechaEntrega : null,
                'Estatus' => ($request->Region == 'Interno') ? $request->Estatus : 'Entregado',
                'region' => $request->Region,
                'IdTipoDocumento' => $request->TipoDocumento['IdTipoDocumento'],
                'IdPeriodoEscolar' => $request->PeriodoEscolar['IdPeriodoEscolar'],
                'IdExpediente' => $request->Expediente['IdExpediente'],
                'IdDepartamento' => ($request->Region == 'Externo') ? null : $request->Departamento['IdDepartamento'],
                'IdUsuario' => Auth::user()->id,
                'URL' => asset('storage/documents/' . $nombreArchivo),
                'Dependencia' => ($request->Region == 'Interno') ? '' : $request->Dependencia,
            ]);
            event(new Registered($document));
            $expediente = expediente::where('IdExpediente', $request->Expediente['IdExpediente'])->first();
            $expediente->numDocumentos = $expediente->numDocumentos + 1;
            $expediente->save();
        }
        return Redirect::route('nuevoDocumento')->with('exitoDocumento', 'Se ha registrado el documento con éxito.');
    }

    public function editarDocumento(Request $request)
    {
        $documento = document::where('IdDocumento', $request->IdDocumento)->first();
        //dd($documento);
        $expediente = expediente::where('IdExpediente', $documento->IdExpediente)->first();
        if ($request->Archivo != '') {
            $nombreArchivo = explode('documents/', $documento->URL);
            if (Storage::exists('public/documents/' . $nombreArchivo[1])) {
                Storage::delete('public/documents/' . $nombreArchivo[1]);
            }
            $fecha = new DateTime(); // Obtener la fecha actual como objeto DateTime
            $archivo = $request->Archivo;
            $nombreArchivo = date_format($fecha, 'Y-m-d_H_i_s') . '_' . $archivo->getClientOriginalName();
            $path = Storage::putFileAs('public/documents', $archivo, $nombreArchivo);
            if (Storage::exists($path)) {
                $documento->URL = asset('storage/documents/' . $nombreArchivo);
            }
        }
        if ($expediente->IdExpediente != $request->Expediente['IdExpediente']) {
            $expediente->numDocumentos = $expediente->numDocumentos - 1;
            $expediente->save();
            $nuevoExpediente = expediente::where('IdExpediente', $documento->IdExpediente)->first();
            $nuevoExpediente->numDocumentos = $nuevoExpediente->numDocumentos + 1;
            $nuevoExpediente->save();
        }
        $documento->Titulo = $request->Titulo;
        $documento->FechaExpedicion = $request->FechaExpedicion;
        $documento->FechaEntrega = ($request->Estatus == 'Entregado') ? $request->FechaEntrega : null;
        $documento->Estatus = ($request->Region == 'Interno') ? $request->Estatus : 'Entregado';
        $documento->region = $request->Region;
        $documento->IdTipoDocumento = $request->TipoDocumento['IdTipoDocumento'];
        $documento->IdPeriodoEscolar = $request->PeriodoEscolar['IdPeriodoEscolar'];
        $documento->IdExpediente = $request->Expediente['IdExpediente'];
        $documento->IdDepartamento = ($request->Region == 'Externo') ? null : $request->Departamento['IdDepartamento'];
        $documento->Dependencia = ($request->Region == 'Interno') ? '' : $request->Dependencia;
        
        $documento->save();
    }

    public function validarDocumento(Request  $request)
    {
        $request->validate([
            'Expediente' => 'required',
            'TipoDocumento' => 'required',
            'Titulo' => 'required',
            'FechaExpedicion' => 'required',
            'Region' => 'required',
            'PeriodoEscolar' => 'required',
        ]);
        if ($request->Archivo != '') {
            if (strpos($request->Archivo->getClientOriginalName(), '.pdf') == false) {
                throw ValidationException::withMessages([
                    'Archivo' => 'Debes de ingresar un archivo PDF',
                ]);
            }
        }
        if ($request->Region == 'Interno') {
            $request->validate([
                'Departamento' => 'required',
                'Estatus' => 'required',
            ]);
        } else {
            $request->validate([
                'Dependencia' => 'required',
            ]);
        }
        if ($request->Estatus == 'Entregado') {
            $request->validate([
                'FechaEntrega' => 'required',
            ]);
            if ($request->FechaExpedicion > $request->FechaEntrega) {
                throw ValidationException::withMessages([
                    'FechaExpedicion' => 'Las fecha de expedición no puede ser despues de la fecha de entrega',
                    'FechaEntrega' => 'Las fecha de entrega no puede ser antes de la fecha de expedición',
                ]);
            }
        }
    }

    public function entregarDocumento(Request $request)
    {
        $documento = document::where('IdDocumento', $request->IdDocumento)->first();
        if ($request->Archivo != '') {
            $nombreArchivo = explode('documents/', $documento->URL);

            if (Storage::exists('public/documents/' . $nombreArchivo[1])) {
                Storage::delete('public/documents/' . $nombreArchivo[1]);
            }

            $fecha = new DateTime(); // Obtener la fecha actual como objeto DateTime
            $archivo = $request->Archivo;
            $nombreArchivo = date_format($fecha, 'Y-m-d_H_i_s') . '_' . $archivo->getClientOriginalName();
            //dd($nombreArchivo);
            $path = Storage::putFileAs('public/documents', $archivo, $nombreArchivo);
            //$path2 = Storage::url($nombreArchivo);
            if (Storage::exists($path)) {
                $documento->URL = asset('storage/documents/' . $nombreArchivo);
            }
        }
        $documento->FechaEntrega = $request->FechaEntrega;
        $documento->Estatus = 'Entregado';
        $documento->save();
    }

    public function validarEntrega(Request $request)
    {
        $request->validate([
            'FechaEntrega' => 'required',
        ]);
        if ($request->FechaExpedicion > $request->FechaEntrega) {
            throw ValidationException::withMessages([
                'FechaEntrega' => 'Las fecha de entrega no puede ser antes de la fecha de expedición',
            ]);
        }
        if ($request->Archivo != '') {
            if (strpos($request->Archivo->getClientOriginalName(), '.pdf') == false) {
                throw ValidationException::withMessages([
                    'Archivo' => 'Debes de ingresar un archivo PDF',
                ]);
            }
        }
    }
}
