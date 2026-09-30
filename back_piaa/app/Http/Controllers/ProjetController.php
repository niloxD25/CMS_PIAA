<?php

namespace App\Http\Controllers;

use App\Models\Projets;
use App\Models\ProjetEnCours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ApiResponse;

class ProjetController extends Controller
{
    /**
     * Récupère tous les projets avec toutes leurs relations
     * GET /api/projets
     */
    public function index(Request $request)
    {
        $niveauAcces = $request->get('niveau_acces');
        
        $projets = Projets::withAllRelations($niveauAcces)->get();

        return ApiResponse::success($projets, "Liste des projets récupérée avec succès");
    }

    /**
     * Crée un nouveau projet
     * POST /api/projets
     */
    public function store(Request $request)
    {
        $validator = $this->validateProjet($request);
        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        $data = $validator->validated();

        // Gestion du logo
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('projets', 'public');
            $data['logo'] = '/storage/' . $path;
        }

        try {
            $projet = DB::transaction(function () use ($data) {
                $projet = Projets::create($data);
                return $projet->loadWithNiveauAcces();
            });

            return ApiResponse::success($projet, "Projet créé avec succès", 201);

        } catch (\Exception $e) {
            // Nettoyage du fichier en cas d'erreur
            if (isset($data['logo']) && Storage::disk('public')->exists(str_replace('/storage/', '', $data['logo']))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $data['logo']));
            }
            return ApiResponse::error("Erreur lors de la création du projet", $e->getMessage(), 500);
        }
    }

    /**
     * Récupère un projet spécifique
     * GET /api/projets/{id}
     */
    public function show(Request $request, $id)
    {
        $niveauAcces = $request->get('niveau_acces');
        
        $projet = Projets::find($id);

        if (!$projet) {
            return ApiResponse::error("Projet non trouvé", null, 404);
        }

        $projet = $projet->loadWithNiveauAcces($niveauAcces);

        return ApiResponse::success($projet, "Détails du projet récupérés avec succès");
    }

    /**
     * Met à jour un projet existant
     * PUT /api/projets/{id}
     */
    public function update(Request $request, Projets $projet)
    {
        $validator = $this->validateProjet($request, true);
        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        $data = $validator->validated();
        $oldLogo = $projet->logo;
        $newLogoPath = null;

        // Gestion du nouveau logo
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('projets', 'public');
            $newLogoPath = '/storage/' . $path;
            $data['logo'] = $newLogoPath;
        }

        try {
            DB::transaction(function () use ($data, $projet) {
                $projet->update($data);
            });

            // Supprimer l'ancien logo après succès de la mise à jour
            if ($newLogoPath && $oldLogo && Storage::disk('public')->exists(str_replace('/storage/', '', $oldLogo))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldLogo));
            }

            $niveauAcces = $request->get('niveau_acces');
            $projet = $projet->loadWithNiveauAcces($niveauAcces);

            return ApiResponse::success($projet, "Projet mis à jour avec succès");

        } catch (\Exception $e) {
            // Rollback du nouveau logo en cas d'erreur
            if ($newLogoPath && Storage::disk('public')->exists(str_replace('/storage/', '', $newLogoPath))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $newLogoPath));
            }

            return ApiResponse::error("Erreur lors de la mise à jour du projet", $e->getMessage(), 500);
        }
    }

    /**
     * Supprime un projet
     * DELETE /api/projets/{id}
     */
    public function destroy(Projets $projet)
    {
        try {
            $logoPath = $projet->logo;
            
            DB::transaction(function () use ($projet) {
                $projet->delete();
            });

            // Supprimer le logo après suppression du projet
            if ($logoPath && Storage::disk('public')->exists(str_replace('/storage/', '', $logoPath))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $logoPath));
            }

            return ApiResponse::success(null, "Projet supprimé avec succès");

        } catch (\Exception $e) {
            return ApiResponse::error("Erreur lors de la suppression du projet", $e->getMessage(), 500);
        }
    }

    /**
     * Définit le projet actuel
     * POST /api/projets/actuel
     */
    public function setProjetActuel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'projet_id' => 'required|integer|exists:projets,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        try {
            $data = $validator->validated();

            $projetEnCours = ProjetEnCours::create([
                'projet_id' => $data['projet_id'],
                'date_changement' => now(),
            ]);

            return ApiResponse::success(
                $projetEnCours->load('projets'),
                "Projet actuel défini avec succès",
                201
            );

        } catch (\Exception $e) {
            return ApiResponse::error("Erreur lors de la définition du projet actuel", $e->getMessage(), 500);
        }
    }

    /**
     * Récupère le projet actuel
     * GET /api/projets/actuel
     */
    public function getProjetActuel()
    {
        $projetEnCours = ProjetEnCours::with('projets')
            ->latest('date_changement')
            ->first();

        if (!$projetEnCours) {
            return ApiResponse::error("Aucun projet actuel défini", null, 404);
        }

        return ApiResponse::success($projetEnCours, "Projet actuel récupéré avec succès");
    }

    /**
     * Récupère le projet actuel avec toutes les relations
     * GET /api/projets/actuelFullData
     */
    public function getProjetActuelWithFullData(Request $request)
    {
        $niveauAcces = $request->get('niveau_acces');
        
        $projetEnCours = ProjetEnCours::latest('date_changement')->first();

        if (!$projetEnCours) {
            return ApiResponse::error("Aucun projet actuel défini", null, 404);
        }

        $projet = Projets::find($projetEnCours->projet_id);
        
        if (!$projet) {
            return ApiResponse::error("Projet associé non trouvé", null, 404);
        }

        $projet = $projet->loadWithNiveauAcces($niveauAcces);

        return ApiResponse::success($projet, "Projet actuel avec toutes les données récupéré avec succès");
    }

    /**
     * Récupère les autres projets avec toutes les relations
     * GET /api/projets/others
     */
    public function getOthersProjetsWithFullData(Request $request)
    {
        $niveauAcces = $request->get('niveau_acces');
        
        $projetEnCours = ProjetEnCours::latest('date_changement')->with('projets')->first();

        if (!$projetEnCours) {
            return ApiResponse::error("Aucun projet actuel défini", null, 404);
        }

        $projets = Projets::whereNotIn('id', [$projetEnCours->projets->id])
                            ->where('archived', false)
                            ->get();
        
        if ($projets->isEmpty()) {
            return ApiResponse::error("Aucun projet trouvé", null, 404);
        }

        // Boucle sur chaque projet pour charger les relations avec le niveau d'accès
        $projets->each(function ($projet) use ($niveauAcces) {
            $projet->loadWithNiveauAcces($niveauAcces);
        });

        return ApiResponse::success($projets, "Autres projets avec toutes les données récupérés avec succès");
    }

    /**
     * Récupère les fichiers d'un projet avec filtre de niveau d'accès
     * GET /api/projets/{id}/fichiers
     */
    public function getFichiers(Request $request, $id)
    {
        $niveauAcces = $request->get('niveau_acces');
        $projet = Projets::find($id);

        if (!$projet) {
            return ApiResponse::error("Projet non trouvé", null, 404);
        }

        $fichiers = $projet->getAllFichiersAttribute($niveauAcces);

        return ApiResponse::success($fichiers, "Fichiers du projet récupérés avec succès");
    }

    /**
     * Validation des données du projet
     */
    private function validateProjet(Request $request, $isUpdate = false)
    {
        $rules = [
            'nom' => ($isUpdate ? 'sometimes|' : '') . 'required|string|max:200',
            'description' => 'nullable|string',
            'logo' => ($isUpdate ? 'sometimes|' : 'nullable|') . 'file|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'debut' => 'nullable|date',
            'duree_annee' => 'nullable|integer|min:0',
        ];

        return Validator::make($request->all(), $rules);
    }
}