<?php

namespace App\Http\Controllers;

use App\Models\Evenements;
use App\Models\ChangementEtatEvenement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiResponse;

class EvenementController extends Controller
{
    /**
     * Récupère la liste des événements avec leurs relations
     */
    public function index(Request $request)
    {
        $query = Evenements::with([
            'projets',
            'sections',
            'changement_etat_evenements.etats_evenement',
            'etatCourant.etats_evenement',
            'fichiers'
        ]);

        if ($request->has('projet_id')) {
            $query->where('projet_id', $request->projet_id);
        }

        return ApiResponse::success(
            $query->get(),
            "Liste des événements récupérée avec succès"
        );
    }

    /**
     * Crée un nouvel événement avec gestion transactionnelle
     */
    public function store(Request $request)
    {
        $validator = $this->validateEvenement($request);

        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('evenements', 'public'); 
            $data['image'] = '/storage/' . $path; // accessible publiquement
        }

        try {
            $evenement = DB::transaction(function () use ($data) {
                $evenement = Evenements::create($data);

                ChangementEtatEvenement::create([
                    'evenement_id' => $evenement->id,
                    'etat_evenement_id' => $data['etat_evenement_id'] ?? 1,
                    'date' => now(),
                ]);

                return $evenement->load(['projets','sections','changement_etat_evenements.etats_evenement','etatCourant.etats_evenement','fichiers']);
            });

            return ApiResponse::success($evenement, "Événement créé avec succès", 201);
        } catch (\Exception $e) {
            return ApiResponse::error("Erreur lors de la création de l'événement", $e->getMessage(), 500);
        }
    }


    /**
     * Récupère un événement spécifique
     */
    public function show($id)
    {
        $evenement = Evenements::with([
            'projets',
            'sections',
            'changement_etat_evenements.etats_evenement',
            'etatCourant.etats_evenement',
            'fichiers'
        ])->find($id);

        if (!$evenement) {
            return ApiResponse::error(
                "Événement non trouvé",
                null,
                404
            );
        }

        return ApiResponse::success(
            $evenement,
            "Détails de l'événement"
        );
    }

    /**
     * Met à jour un événement existant
     */
    public function update(Request $request, Evenements $evenement)
    {
        $validator = $this->validateEvenement($request, true);

        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        $data = $validator->validated();
        $oldImage = $evenement->image;
        $newImagePath = null;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('evenements', 'public'); 
            $newImagePath = '/storage/' . $path;
            $data['image'] = $newImagePath;
        }

        try {
            DB::transaction(function () use ($data, $evenement) {
                $evenement->update($data);

                if (isset($data['etat_evenement_id']) &&
                    $evenement->etatCourant->etat_evenement_id !== $data['etat_evenement_id']) {
                    ChangementEtatEvenement::create([
                        'evenement_id' => $evenement->id,
                        'etat_evenement_id' => $data['etat_evenement_id'],
                        'date' => now(),
                    ]);
                }
            });

            if ($newImagePath && $oldImage && \Storage::disk('public')->exists(str_replace('/storage/', '', $oldImage))) {
                \Storage::disk('public')->delete(str_replace('/storage/', '', $oldImage));
            }

            return ApiResponse::success(
                $evenement->fresh()->load([
                    'projets',
                    'sections',
                    'changement_etat_evenements.etats_evenement',
                    'etatCourant.etats_evenement',
                    'fichiers'
                ]),
                "Événement mis à jour avec succès"
            );

        } catch (\Exception $e) {
            // 🔹 En cas d’échec, supprimer le nouveau fichier uploadé (rollback manuel des fichiers)
            if ($newImagePath && \Storage::disk('public')->exists(str_replace('/storage/', '', $newImagePath))) {
                \Storage::disk('public')->delete(str_replace('/storage/', '', $newImagePath));
            }

            return ApiResponse::error(
                "Erreur lors de la mise à jour de l'événement",
                $e->getMessage(),
                500
            );
        }
    }


    /**
     * Supprime un événement (soft delete)
     */
    public function destroy(Evenements $evenement)
    {
        try {
            $evenement->delete();
            return ApiResponse::success(null, "Événement supprimé avec succès", 200);
        } catch (\Exception $e) {
            return ApiResponse::error(
                "Erreur lors de la suppression de l'événement",
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Liste des sections d'un événement
     * GET /api/evenements/{id}/sections
     */
    public function getSections($id)
    {
        $evenement = Evenements::find($id);

        if (!$evenement) {
            return ApiResponse::error("Événement non trouvé", null, 404);
        }

        return ApiResponse::success(
            $evenement->sections,
            "Sections de l'événement récupérées avec succès"
        );
    }

    /**
     * Créer une section pour un événement
     * POST /api/evenements/{id}/sections
     */
    public function addSection(Request $request, $id)
    {
        $evenement = Evenements::find($id);
        if (!$evenement) return ApiResponse::error("Événement non trouvé", null, 404);

        $validator = $this->validateSection($request);
        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('sections', 'public');
            $data['image'] = '/storage/' . $path;
        }

        try {
            $section = $evenement->sections()->create($data);
            return ApiResponse::success($section, "Section ajoutée avec succès", 201);

        } catch (\Exception $e) {
            // rollback image si DB échoue
            if (isset($data['image']) && \Storage::disk('public')->exists(str_replace('/storage/', '', $data['image']))) {
                \Storage::disk('public')->delete(str_replace('/storage/', '', $data['image']));
            }
            return ApiResponse::error("Erreur lors de la création de la section", $e->getMessage(), 500);
        }
    }

    /**
     * Mettre à jour une section
     * PUT /api/evenements/{evenementId}/sections/{sectionId}
     */
    public function updateSection(Request $request, $evenementId, $sectionId)
    {
        $evenement = Evenements::find($evenementId);
        if (!$evenement) return ApiResponse::error("Événement non trouvé", null, 404);

        $section = $evenement->sections()->find($sectionId);
        if (!$section) return ApiResponse::error("Section non trouvée", null, 404);

        $validator = $this->validateSection($request, true);
        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        $data = $validator->validated();
        $oldImage = $section->image;
        $newImagePath = null;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('sections', 'public');
            $newImagePath = '/storage/' . $path;
            $data['image'] = $newImagePath;
        }

        try {
            DB::transaction(function () use ($data, $section) {
                $section->update($data);
            });

            // 🔹 suppression de l’ancienne image après succès
            if ($newImagePath && $oldImage && \Storage::disk('public')->exists(str_replace('/storage/', '', $oldImage))) {
                \Storage::disk('public')->delete(str_replace('/storage/', '', $oldImage));
            }

            return ApiResponse::success($section->fresh(), "Section mise à jour avec succès");

        } catch (\Exception $e) {
            // rollback fichier si erreur DB
            if ($newImagePath && \Storage::disk('public')->exists(str_replace('/storage/', '', $newImagePath))) {
                \Storage::disk('public')->delete(str_replace('/storage/', '', $newImagePath));
            }

            return ApiResponse::error("Erreur lors de la mise à jour de la section", $e->getMessage(), 500);
        }
    }

    /**
     * Supprimer une section
     * DELETE /api/evenements/{evenementId}/sections/{sectionId}
     */
    public function deleteSection($evenementId, $sectionId)
    {
        $evenement = Evenements::find($evenementId);

        if (!$evenement) {
            return ApiResponse::error("Événement non trouvé", null, 404);
        }

        $section = $evenement->sections()->find($sectionId);

        if (!$section) {
            return ApiResponse::error("Section non trouvée", null, 404);
        }

        $section->delete();

        return ApiResponse::success(null, "Section supprimée avec succès");
    }

    /**
     * Validation évènements
     */
    private function validateEvenement(Request $request, $isUpdate = false)
    {
        $rules = [
            'titre'        => ($isUpdate ? 'sometimes|' : '') . 'required|string|max:200',
            'description'  => 'nullable|string',
            'date_debut'   => ($isUpdate ? 'sometimes|' : '') . 'required|date',
            'date_fin'     => 'nullable|date|after_or_equal:date_debut',
            'image'        => ($isUpdate ? 'sometimes|' : 'nullable|') . 'file|mimes:jpg,jpeg,png',
            'projet_id'    => ($isUpdate ? 'sometimes|' : '') . 'required|integer|exists:projets,id',
            'niveau_acces' => 'nullable|integer',
            'archived'     => 'boolean',
            'etat_evenement_id' => 'nullable|integer|exists:etats_evenement,id'
        ];

        return Validator::make($request->all(), $rules);
    }

    /**
     * Validation sections
     */
    private function validateSection(Request $request, $isUpdate = false)
    {
        $rules = [
            'titre' => ($isUpdate ? 'sometimes|' : 'required|') . 'string|max:200',
            'description' => 'nullable|string',
            'image' => ($isUpdate ? 'sometimes|' : 'nullable|') . 'file|mimes:jpg,jpeg,png',
            'evenement_id' => ($isUpdate ? 'sometimes|' : '') . 'integer|exists:evenements,id',
        ];

        return Validator::make($request->all(), $rules);
    }

}
