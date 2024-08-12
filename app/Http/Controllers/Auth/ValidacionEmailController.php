<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ValidacionEmailController extends Controller
{
    /**
     * Este metodo se invoca para poder autenticar y validar un usuario que fue creado por el administrador y posteriormente retornarlo a la vista principal
     */
    public function __invoke($request): RedirectResponse
    {   
        Auth::loginUsingId($request);
        $user = User::find(Auth::user()->id);
        if ($user->hasVerifiedEmail()) {
            return Redirect::route('dashboard');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return Redirect::route('dashboard');
    }
}
