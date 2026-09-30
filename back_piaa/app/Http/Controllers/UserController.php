<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiResponse;

class UserController extends Controller
{
    /* ==============================
       GET /api/users
       Récupère tous les utilisateurs
    ============================== */
    public function index(Request $request)
    {
        $query = User::with('typeCompte');

        if ($request->has('type_compte_id')) {
            $role = $request->query('type_compte_id');
            $query->whereHas('typeCompte', function ($q) use ($role) {
                $q->where('type_compte_id', $role);
            });
        }

        $users = $query->get();

        return ApiResponse::success($users, "Liste des utilisateurs récupérée avec succès");
    }

    /* ==============================
       GET /api/users/{id}
       Récupère un utilisateur spécifique
    ============================== */
    public function show($id)
    {
        $user = User::with('typeCompte')->find($id);

        if (!$user) {
            return ApiResponse::error("Utilisateur non trouvé", null, 404);
        }

        return ApiResponse::success($user, "Utilisateur récupéré avec succès");
    }

    /* ==============================
       POST /api/users
       Crée un nouvel utilisateur
    ============================== */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'type_compte_id' => 'required|exists:types_compte,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        $data = $validator->validated();
        $data['password'] = Hash::make($data['password']);

        try {
            $user = User::create($data);
            $user->load('typeCompte');
            return ApiResponse::success($user, "Utilisateur créé avec succès", 201);
        } catch (\Exception $e) {
            return ApiResponse::error("Erreur lors de la création", $e->getMessage(), 500);
        }
    }

    /* ==============================
       PUT /api/users/{id}
       Met à jour un utilisateur
    ============================== */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return ApiResponse::error("Utilisateur non trouvé", null, 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:6|confirmed',
            'type_compte_id' => 'sometimes|required|exists:types_compte,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        $data = $validator->validated();
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        try {
            $user->update($data);
            $user->load('typeCompte');
            return ApiResponse::success($user, "Utilisateur mis à jour avec succès");
        } catch (\Exception $e) {
            return ApiResponse::error("Erreur lors de la mise à jour", $e->getMessage(), 500);
        }
    }

    /* ==============================
       DELETE /api/users/{id}
       Supprime un utilisateur
    ============================== */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return ApiResponse::error("Utilisateur non trouvé", null, 404);
        }

        try {
            $user->delete();
            return ApiResponse::success(null, "Utilisateur supprimé avec succès");
        } catch (\Exception $e) {
            return ApiResponse::error("Erreur lors de la suppression", $e->getMessage(), 500);
        }
    }
}
