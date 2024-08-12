<?php

namespace App\Http\Controllers;

use App\Models\document;
use App\Models\Personal;
use App\Models\Secretaria;
use App\Models\TipoDocumento;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class tipoDocumentoController extends Controller
{
    //
    public function index(){
        $user = User::find(Auth::user()->id);
        $personal = Personal::where('IdPersonal', Auth::user()->IdPersonal)->first();
        $tipoDocs = TipoDocumento::with('documento')
                    ->withCount('documento as numDocumentos')
                    ->get();
        //dd($tipoDocs);
        if(Secretaria::where('IdPersonal', Auth::user()->IdPersonal)->first()!==null){
            return Inertia::render('Dashboard_secre_tipoDoc',['user'=>$user,'personal'=>$personal, 'tipoDocs'=>$tipoDocs]);
        }
        return Inertia::render('Dashboard_admin_tipoDoc',['user'=>$user,'personal'=>$personal, 'tipoDocs'=>$tipoDocs]);
    }

    public function nuevoTipoDoc(Request $request){
        $this->validacionTipoDoc($request);
        
        $tipoDoc = TipoDocumento::create([
            "nombreTipoDoc" => $request ->nombreTipoDoc,
        ]);
        event(new Registered($tipoDoc));
        return Redirect::route('tipoDoc');
    }
    
    public function editarTipoDoc(Request $request){
        $this->validacionTipoDoc($request);

        $tipoDoc = TipoDocumento::where('IdTipoDocumento', $request->idtipoDoc)->first();

        $tipoDoc->nombreTipoDoc = $request->nombreTipoDoc;
        $tipoDoc->save();
    }

    public function validacionTipoDoc(Request $request){
        $request->validate([
            'nombreTipoDoc' => 'required|string|max:100|unique:'.TipoDocumento::class,
        ]);
    }

    public function borrarTipoDoc(Request $request){
        $tipoDoc = TipoDocumento::where('IdTipoDocumento', $request->idtipoDoc)->first();
        $tipoDoc->delete();
    }
}
