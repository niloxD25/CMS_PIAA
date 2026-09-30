<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiResponse;
use App\Models\UserLogins;

class AuthController extends Controller
{
    /* =====================
       POST /api/register
    ===================== */
    public function register(Request $request)
    {
        $validator = $this->validateData($request, 'register');
        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        $data = $validator->validated();
        $data['password'] = Hash::make($data['password']);

        try {
            $user = User::create($data);
            $user->load('typeCompte');

            $token = $user->createToken('api_token');

            $user->logins()->create([
                'ip_address'    => $request->ip(),
                'user_agent'    => $request->userAgent(),
                'session_id'    => $token->accessToken->id,
                'connection_type' => UserLogins::TYPE_API,
                'logged_in_at'  => now(),
                'is_active'     => true,
            ]);

            return ApiResponse::success([
                'user' => $user,
                'token' => $token->plainTextToken,
            ], "Utilisateur enregistré avec succès", 201);
        } catch (\Exception $e) {
            return ApiResponse::error("Erreur lors de l'enregistrement", $e->getMessage(), 500);
        }
    }

    /* ===================
       POST /api/login
    =================== */
    public function login(Request $request)
    {
        $validator = $this->validateData($request, 'login');
        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        $data = $validator->validated();
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return ApiResponse::error("Identifiants incorrects", null, 401);
        }

        $user->load('typeCompte');

        $token = $user->createToken('api_token');

        $user->logins()->create([
            'ip_address'    => $request->ip(),
            'user_agent'    => $request->userAgent(),
            'session_id'    => $token->accessToken->id,
            'connection_type' => UserLogins::TYPE_API,
            'logged_in_at'  => now(),
            'is_active'     => true,
        ]);

        return ApiResponse::success([
            'user' => $user,
            'token' => $token->plainTextToken,
        ], "Connexion réussie");
    }

    /* ===================
       POST /api/logout
    =================== */
    public function logout(Request $request)
    {
        try {
            $currentToken = $request->user()->currentAccessToken();

            if ($currentToken) {
                $request->user()->logins()
                    ->where('session_id', $currentToken->id)
                    ->where('is_active', true)
                    ->update([
                        'is_active'     => false,
                        'logged_out_at' => now(),
                    ]);

                $currentToken->delete();
            }

            return ApiResponse::success(null, "Déconnecté avec succès");
        } catch (\Exception $e) {
            return ApiResponse::error("Erreur lors de la déconnexion", $e->getMessage(), 500);
        }
    }

    /* ================
       GET /api/me
    ================ */
    public function me(Request $request)
    {
        $user = $request->user()->load('typeCompte');
        return ApiResponse::success($user, "Informations utilisateur récupérées avec succès");
    }

    /* ===========================
        POST /api/admin-login
    =========================== */
    public function adminLogin(Request $request)
    {
        $validator = $this->validateData($request, 'login');
        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        $data = $validator->validated();

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return ApiResponse::error("Identifiants incorrects", null, 401);
        }

        // Vérifie que l'utilisateur est admin ou content manager
        $user->load('typeCompte');
        if (!in_array($user->typeCompte->id, [1, 2])) {
            return ApiResponse::error("Accès refusé", null, 403);
        }

        $token = $user->createToken('api_token');

        $user->logins()->create([
            'ip_address'    => $request->ip(),
            'user_agent'    => $request->userAgent(),
            'session_id'    => $token->accessToken->id,
            'connection_type' => UserLogins::TYPE_API,
            'logged_in_at'  => now(),
            'is_active'     => true,
        ]);

        return ApiResponse::success([
            'user' => $user,
            'token' => $token->plainTextToken,
        ], "Connexion réussie");
    }

    /* =============
       Validation
    ============= */
    private function validateData(Request $request, $type)
    {
        $rules = [];

        switch ($type) {
            case 'register':
                if (!$request->has('type_compte_id') || $request->type_compte_id === null) {
                    $request->merge(['type_compte_id' => 4]);
                }
                
                $rules = [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|string|min:8|confirmed',
                    'type_compte_id' => 'required|exists:types_compte,id',
                ];
                break;

            case 'login':
                $rules = [
                    'email' => 'required|email',
                    'password' => 'required|string',
                ];
                break;
        }

        return Validator::make($request->all(), $rules);
    }
}
