<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EtatsEvenement;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Helpers\ApiResponse;

class EtatEvenementController extends Controller
{
    /**
     * Récupère tous les états d'événement
     * GET /api/etats-evenement
     */
    public function index()
    {
        $etats = EtatsEvenement::all();
        return ApiResponse::success($etats, "Liste des états d'événement récupérée avec succès");
    }

    /**
     * Crée un nouvel état d'événement
     * POST /api/etats-evenement
     */
    public function store(Request $request)
    {
        $validator = $this->validateEtatEvenement($request);

        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        $etatEvenement = EtatsEvenement::create($validator->validated());

        return ApiResponse::success($etatEvenement, "État d'événement créé avec succès", 201);
    }

    /**
     * Récupère un état d'événement spécifique
     * GET /api/etats-evenement/{id}
     */
    public function show($id)
    {
        $etatEvenement = EtatsEvenement::find($id);

        if (!$etatEvenement) {
            return ApiResponse::error("État d'événement non trouvé", null, 404);
        }

        return ApiResponse::success($etatEvenement, "Détails de l'état d'événement");
    }

    /**
     * Met à jour un état d'événement existant
     * PUT /api/etats-evenement/{id}
     */
    public function update(Request $request, $id)
    {
        $etatEvenement = EtatsEvenement::find($id);

        if (!$etatEvenement) {
            return ApiResponse::error("État d'événement non trouvé", null, 404);
        }

        $validator = $this->validateEtatEvenement($request, true, $etatEvenement->id);

        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        $etatEvenement->update($validator->validated());
        
        return ApiResponse::success($etatEvenement, "État d'événement mis à jour avec succès");
    }

    /**
     * Supprime un état d'événement
     * DELETE /api/etats-evenement/{id}
     */
    public function destroy($id)
    {
        $etatEvenement = EtatsEvenement::find($id);

        if (!$etatEvenement) {
            return ApiResponse::error("État d'événement non trouvé", null, 404);
        }

        // Vérification de l'utilisation
        if ($etatEvenement->changement_etat_evenements()->exists()) {
            return ApiResponse::error(
                "Impossible de supprimer cet état car il est utilisé dans des changements d'état",
                null,
                422
            );
        }

        $etatEvenement->delete();

        return ApiResponse::success(null, "État d'événement supprimé avec succès");
    }

    /**
     * Validation Etats Evenement
     */
    private function validateEtatEvenement(Request $request, $isUpdate = false, $etatId = null)
    {
        $rules = [
            'etat' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:100',
                Rule::unique('etats_evenement', 'etat')->ignore($etatId),
            ],
        ];

        return Validator::make($request->all(), $rules);
    }
}
