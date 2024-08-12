<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Models\Personal;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = User::find(Auth::user()->id);
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back();
    }
    public function firstPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', Password::defaults(), 'confirmed'],
            'Departamento' => 'required',
            'Sexo' => 'required',
        ]);
        $user = User::find(Auth::user()->id);
        $personal = Personal::where('IdPersonal', $user->IdPersonal)->first();
        $personal->IdDepartamento = $request->Departamento['IdDepartamento'];
        $personal->Sexo = $request->Sexo;
        $personal->save();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);
        return back();
    }
}
