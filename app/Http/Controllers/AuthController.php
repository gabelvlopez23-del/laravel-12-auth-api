<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validamos que los datos cumplan con los requisitos necesarios
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        // Registramos al nuevo usuario en la base de datos SQLite
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Generamos su token personal único
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        // Validamos el formato de las credenciales de entrada
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Buscamos si existe el usuario asociado al correo electrónico
        $user = User::where('email', $data['email'])->first();

        // Si el usuario no existe o la contraseña no es correcta, arrojamos error
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales inválidas.'],
            ]);
        }

        // Opcional: Descomenta la línea de abajo si deseas permitir un solo token por usuario activo
        // $user->tokens()->delete();

        // Generamos el token de la sesión iniciada
        $token = $user->createToken('api')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    public function me(Request $request)
    {
        // Retorna el objeto JSON del usuario actualmente logueado
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        // Revoca y destruye exclusivamente el token que se usó para autorizar esta llamada
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'ok']);
    }
}
