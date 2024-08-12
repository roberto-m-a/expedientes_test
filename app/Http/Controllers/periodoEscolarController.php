<?php

namespace App\Http\Controllers;

use App\Models\PeriodoEscolar;
use App\Models\Personal;
use App\Models\Secretaria;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class periodoEscolarController extends Controller
{
    //
    public function index()
    {
        $periodosEscolares = PeriodoEscolar::with('documento')
            ->withCount('documento as numDocumentos')
            ->get();

        $user = User::find(Auth::user()->id);
        $personal = Personal::where('IdPersonal', Auth::user()->IdPersonal)->first();
        if (Secretaria::where('IdPersonal', Auth::user()->IdPersonal)->first() !== null) {
            return Inertia::render('Dashboard_secre_PeriodoEscolar', ['user' => $user, 'personal' => $personal, 'periodosEscolares' => $periodosEscolares]);
        }
        return Inertia::render('Dashboard_admin_PeriodoEscolar', ['user' => $user, 'personal' => $personal, 'periodosEscolares' => $periodosEscolares]);
    }
    public function nuevoPeriodoEscolar(Request $request)
    {
        $this->validarPeriodoEscolar($request);
            $periodoEscolar = PeriodoEscolar::create([
                'fechaInicio' => $request->fechaInicio,
                'fechaTermino' => $request->fechaTermino,
                'nombre_corto' => $request->nombre_corto,
            ]);
            event(new Registered($periodoEscolar));
            return Redirect::route('periodoEscolar');
    }

    public function editarPeriodoEscolar(Request $request)
    {
        $this->validarPeriodoEscolar($request);
        $periodoEscolar = PeriodoEscolar::where('IdPeriodoEscolar', $request->IdPeriodoEscolar)->first();
        $periodoEscolar->FechaInicio = $request->fechaInicio;
        $periodoEscolar->FechaTermino = $request->fechaTermino;
        $periodoEscolar->nombre_corto = $request->nombre_corto;
        $periodoEscolar->save();
    }

    public function validarPeriodoEscolar(Request $request)
    {
        $request->validate([
            'fechaInicio' => 'required|string|max:50',
            'fechaTermino' => 'required|string|max:50',
            'nombre_corto' => 'required|string|max:50|unique:' . PeriodoEscolar::class,
        ]);

        if ($request->fechaInicio > $request->fechaTermino) {
            throw ValidationException::withMessages([
                'fechaInicio' => 'La fecha de inicio no puede ser despues de la fecha de termino',
                'fechaTermino' => 'La fecha de termino no puede ser antes de la fecha de inicio',
            ]);
        }

        if ($request->fechaInicio == $request->fechaTermino) {
            throw ValidationException::withMessages([
                'fechaInicio' => 'Las fechas no pueden ser iguales',
                'fechaTermino' => 'Las fechas no pueden ser iguales',
            ]);
        }
    }

    public function borrarPeriodoEscolar(Request $request)
    {
        $periodoEscolar = PeriodoEscolar::where('IdPeriodoEscolar', $request->IdPeriodoEscolar);
        $periodoEscolar->delete();
    }
}
