<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Administrador;
use App\Models\Departamento;
use App\Models\Personal;
use App\Models\Secretaria;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = User::find(Auth::user()->id);
        $departamentos = Departamento::all();
        //dd($user->id);
        $personal = Personal::where('IdPersonal', Auth::user()->IdPersonal)->first();
        //dd(Auth::user());
        if(Secretaria::where('IdPersonal', Auth::user()->IdPersonal)->first()!==null){
            return Inertia::render('Profile/Edit_secretaria', [
                'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
                'status' => session('status'),
                'user' =>$user,
                'personal'=>$personal,
                'departamentos'=>$departamentos,
            ]);  
        }
        if(Administrador::where('IdPersonal',Auth::user()->IdPersonal)->first()!==null){
            return Inertia::render('Profile/Edit_admin', [
                'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
                'status' => session('status'),
                'user' =>$user,
                'personal'=>$personal,
                'departamentos'=>$departamentos,
            ]);    
        }
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'user' =>$user,
            'personal'=>$personal,
            'departamentos'=>$departamentos,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());
        $personal = Personal::where('IdPersonal', Auth::user()->IdPersonal)->first();
        $personal->Nombre = $request->name;
        $personal->Apellidos = $request ->lastname;
        $personal->IdDepartamento = $request->Departamento['IdDepartamento'];
        $personal->Sexo = $request->Sexo;
        $personal->save();
        
        if(strpos($request->email, '@itoaxaca.edu.mx')==false && strpos($request->email, '@oaxaca.tecnm.mx')==false ){
            throw ValidationException::withMessages([
                'email' => 'El dominio debe ser de la institución (@itoaxaca.edu.mx o @oaxaca.tecnm.mx)',
            ]);
        }else{
            if ($request->user()->isDirty('email')) {
                $request->user()->email_verified_at = null;
            }
            $request->user()->save();
        }
        return Redirect::route('profile.edit');
    }
}
