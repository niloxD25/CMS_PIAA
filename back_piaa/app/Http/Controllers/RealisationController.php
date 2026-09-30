<?php

namespace App\Http\Controllers;

use App\Models\Realisations;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RealisationController extends Controller
{
    /**
     * Liste toutes les réalisations
     * GET /api/realisations
     */
    public function index()
    {
        $realisations = Realisations::with('projets')->get();
        return ApiResponse::success($realisations, "Liste des réalisations récupérée avec succès");
    }

    /**
     * Crée une nouvelle réalisation
     * POST /api/realisations
     */
    public function store(Request $request)
    {
        $validator = $this->validateRealisations($request);

        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        try {
            $realisation = DB::transaction(function () use ($validator) {
                return Realisations::create($validator->validated());
            });

            return ApiResponse::success($realisation->load('projets'), "Réalisation créée avec succès", 201);

        } catch (\Exception $e) {
            return ApiResponse::error("Erreur lors de la création de la réalisation", $e->getMessage(), 500);
        }
    }

    /**
     * Récupère une réalisation spécifique
     * GET /api/realisations/{id}
     */
    public function show($id)
    {
        $realisation = Realisations::with('projets')->find($id);

        if (!$realisation) {
            return ApiResponse::error("Réalisation non trouvée", null, 404);
        }

        return ApiResponse::success($realisation, "Détails de la réalisation");
    }

    /**
     * Met à jour une réalisation
     * PUT /api/realisations/{id}
     */
    public function update(Request $request, $id)
    {
        $realisation = Realisations::find($id);

        if (!$realisation) {
            return ApiResponse::error("Réalisation non trouvée", null, 404);
        }

        $validator = $this->validateRealisations($request, true);

        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        try {
            DB::transaction(function () use ($realisation, $validator) {
                $realisation->update($validator->validated());
            });

            return ApiResponse::success($realisation->fresh()->load('projets'), "Réalisation mise à jour avec succès");

        } catch (\Exception $e) {
            return ApiResponse::error("Erreur lors de la mise à jour de la réalisation", $e->getMessage(), 500);
        }
    }

    /**
     * Supprime une réalisation
     * DELETE /api/realisations/{id}
     */
    public function destroy($id)
    {
        $realisation = Realisations::find($id);

        if (!$realisation) {
            return ApiResponse::error("Réalisation non trouvée", null, 404);
        }

        try {
            $realisation->delete();
            return ApiResponse::success(null, "Réalisation supprimée avec succès");
        } catch (\Exception $e) {
            return ApiResponse::error("Erreur lors de la suppression de la réalisation", $e->getMessage(), 500);
        }
    }

    /**
     * Validation des données Réalisations
     */
    private function validateRealisations(Request $request, $isUpdate = false)
    {
        $rules = [
            'titre'             => ($isUpdate ? 'sometimes|' : 'required|') . 'string|max:255',
            'beneficiaires'     => 'nullable|string',
            'impacts_potentiel' => 'nullable|string',
            'date_realisation'  => 'nullable|date',
            'projet_id'         => ($isUpdate ? 'sometimes|' : 'required|') . 'integer|exists:projets,id',
            'niveau_acces'      => 'nullable|integer|min:0',
            'archived'          => 'nullable|boolean',
        ];

        return Validator::make($request->all(), $rules);
    }
}
