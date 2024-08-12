<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Models\expediente;
use App\Models\Personal;
use App\Models\User;
use App\Notifications\notificacionRegistroCorreo;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    /**
     * Muestra la vista para registrarse.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Este metodo sirve para crear a un usuario Docente desde la vista de registro de usuario de la pagina
     * El usuario debe de introducir al formulario su nombre, sus apellidos y su correo, asi como una confirmacion del correo
     * El correo electronico debe tener el dominio de @itoaxaca.edu.mx o bien @oaxaca.tecnm.mx
     * El metodo redireccionará al dashboard del usuario siempre y cuando el usuario hay verificado su correo electronico
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'lastname' => 'required|string|max:101',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'email_confirmation' => 'required|string|lowercase|email|max:255'
        ]);
        if($request->email !== $request->email_confirmation){
            throw ValidationException::withMessages([
                'email' => 'Los correos no coinciden',
            ]);
        }
        if(strpos($request->email, '@itoaxaca.edu.mx')==false && strpos($request->email, '@oaxaca.tecnm.mx')==false ){
            throw ValidationException::withMessages([
                'email' => 'El dominio debe ser de la institución (@itoaxaca.edu.mx o @oaxaca.tecnm.mx)',
            ]);
        }
        
        $personal = Personal::create([
            'Nombre' => $request->name, 
            'Apellidos' => $request->lastname,
        ]);
        event(new Registered($personal));
        
        $user = User::create([
            'email' => $request->email,
            'IdPersonal' => $personal->IdPersonal,
        ]);
        
        event(new Registered($user));

        $docente = Docente::create([
            'GradoAcademico'=>'Licenciatura',
            'IdPersonal' => $personal ->IdPersonal,
        ]);
        //dd($docente);
        event(new Registered($docente));

        $Expediente = expediente::create([
            'IdDocente' => $docente->IdDocente, 
        ]);

        event(new Registered($Expediente));
        
        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
    /**
     * El metodo permite validar unicamente el correo electronico del usuario para que el administrador pueda añadir
     * el usuario cuando este no se creo durante el registro del personal
     */
    public function validarUsuario(Request $request){
        $request->validate([
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'email_confirmation' => 'required|string|lowercase|email|max:255',
        ]);
        if($request->email !== $request->email_confirmation){
            throw ValidationException::withMessages([
                'email' => 'Los correos no coinciden',
            ]);
        }
        if(strpos($request->email, '@itoaxaca.edu.mx')==false && strpos($request->email, '@oaxaca.tecnm.mx')==false ){
            throw ValidationException::withMessages([
                'email' => 'El dominio debe ser de la institución (@itoaxaca.edu.mx o @oaxaca.tecnm.mx)',
            ]);
        }
    }
    /**
     * Crea al usuario previamente validado y manda una notificacion al correo del usuario para que este verifique su correo. 
     */
    public function aniadirUsuario(Request $request){
        $user = User::create([
            'email' => $request->email,
            'IdPersonal' => $request->IdPersonal,
        ]);
        $user->notify(new notificacionRegistroCorreo());
    }
}
