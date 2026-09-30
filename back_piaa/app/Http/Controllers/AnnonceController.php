<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Annonces;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnnonceController extends Controller
{
    /**
     * Liste toutes les annonces
     * GET /api/annonces
     */
    public function index()
    {
        $annonces = Annonces::all();
        return ApiResponse::success($annonces, "Liste des annonces récupérée avec succès");
    }

    /**
     * Récupère uniquement les annonces à afficher
     * GET /api/annonces/actives
     */
    public function getAnnoncesActives()
    {
        $now = Carbon::now();

        $annonces = Annonces::where('date_affichage', '<=', $now)
            ->whereRaw("(date_affichage + (nbr_jours_affichage || ' days')::interval) >= ?", [$now])
            ->get();

        return ApiResponse::success($annonces, "Annonces actives récupérées avec succès");
    }

    /**
     * Crée une nouvelle annonce
     * POST /api/annonces
     */
    public function store(Request $request)
    {
        $validator = $this->validateData($request);
        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        try {
            $annonce = DB::transaction(function () use ($validator) {
                return Annonces::create($validator->validated());
            });

            return ApiResponse::success($annonce, "Annonce créée avec succès", 201);
        } catch (\Exception $e) {
            return ApiResponse::error("Erreur lors de la création de l'annonce", $e->getMessage(), 500);
        }
    }

    /**
     * Affiche une annonce spécifique
     * GET /api/annonces/{id}
     */
    public function show($id)
    {
        $annonce = Annonces::find($id);
        if (!$annonce) {
            return ApiResponse::error("Annonce non trouvée", null, 404);
        }
        return ApiResponse::success($annonce, "Annonce récupérée avec succès");
    }

    /**
     * Met à jour une annonce
     * PUT/PATCH /api/annonces/{id}
     */
    public function update(Request $request, $id)
    {
        $annonce = Annonces::find($id);
        if (!$annonce) {
            return ApiResponse::error("Annonce non trouvée", null, 404);
        }

        $validator = $this->validateData($request, true);
        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        try {
            DB::transaction(function () use ($annonce, $validator) {
                $annonce->update($validator->validated());
            });

            return ApiResponse::success($annonce->fresh(), "Annonce mise à jour avec succès");
        } catch (\Exception $e) {
            return ApiResponse::error("Erreur lors de la mise à jour de l'annonce", $e->getMessage(), 500);
        }
    }

    /**
     * Supprime une annonce
     * DELETE /api/annonces/{id}
     */
    public function destroy($id)
    {
        $annonce = Annonces::find($id);
        if (!$annonce) {
            return ApiResponse::error("Annonce non trouvée", null, 404);
        }

        try {
            $annonce->delete();
            return ApiResponse::success(null, "Annonce supprimée avec succès");
        } catch (\Exception $e) {
            return ApiResponse::error("Erreur lors de la suppression de l'annonce", $e->getMessage(), 500);
        }
    }

    /**
     * Validation réutilisable pour les annonces
     */
    private function validateData(Request $request, $isUpdate = false)
    {
        $rules = [
            'titre' => ($isUpdate ? 'sometimes|' : 'required|') . 'string|max:255',
            'contenu' => 'nullable|string',
            'date_affichage' => 'nullable|date',
            'nbr_jours_affichage' => 'integer|min:1',
        ];

        return Validator::make($request->all(), $rules);
    }
}
