<?php

namespace App\Http\Controllers;

use App\Models\Fichiers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Helpers\ApiResponse;

class FichierController extends Controller
{
    /**
     * Liste des fichiers
     */
    public function index(Request $request)
    {
        $query = Fichiers::with(['entites_fichier', 'projets']);

        if ($request->has('entite_type_id')) {
            $query->where('entite_type_id', $request->entite_type_id);
        }

        if ($request->has('entite_id')) {
            $query->where('entite_id', $request->entite_id);
        }

        if ($request->has('projet_id')) {
            $query->where('projet_id', $request->projet_id);
        }

        return ApiResponse::success($query->get(), "Liste des fichiers récupérée avec succès");
    }

    /**
     * Créer un fichier
     */
    public function store(Request $request)
    {
        $validator = $this->validateFichier($request);

        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        $data = $validator->validated();
        $path = null;

        try {
            // Gestion de l’upload
            if ($request->hasFile('fichier')) {
                $path = $request->file('fichier')->store('uploads/fichiers', 'public');
                $data['chemin'] = '/storage/' . $path;
                $data['type_fichier'] = $request->file('fichier')->getClientOriginalExtension();
            }

            $fichier = Fichiers::create($data);

            return ApiResponse::success(
                $fichier->load(['entites_fichier', 'projets']),
                "Fichier créé avec succès",
                201
            );

        } catch (\Exception $e) {
            // si erreur DB → supprimer le fichier déjà uploadé
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            return ApiResponse::error("Erreur lors de l’enregistrement", $e->getMessage(), 500);
        }
    }

    /**
     * Détail d’un fichier
     */
    public function show($id)
    {
        $fichier = Fichiers::with(['entites_fichier', 'projets'])->find($id);
        
        if (!$fichier) {
            return ApiResponse::error(
                "Fichier non trouvé",
                null,
                404
            );
        }

        return ApiResponse::success($fichier, "Détails du fichier");
    }

    /**
     * Mettre à jour un fichier
     * Besoin de method spoofing (_method=PUT dans form-data si upload)
     */
    public function update(Request $request, $id)
    {
        $fichier = Fichiers::findOrFail($id);

        $validator = $this->validateFichier($request, true);
        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        $data = $validator->validated();
        $oldPath = $fichier->chemin;
        $newPath = null;

        try {
            DB::beginTransaction();

            // Gestion du remplacement de fichier
            if ($request->hasFile('fichier')) {
                $newPath = $request->file('fichier')->store('uploads/fichiers', 'public');
                $data['chemin'] = '/storage/' . $newPath;
                $data['type_fichier'] = $request->file('fichier')->getClientOriginalExtension();
            }

            $fichier->update($data);

            DB::commit();

            // suppression de l’ancien fichier seulement si update DB réussie
            if ($newPath && $oldPath && Storage::disk('public')->exists(str_replace('/storage/', '', $oldPath))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldPath));
            }

            return ApiResponse::success(
                $fichier->fresh()->load(['entites_fichier', 'projets']),
                "Fichier mis à jour avec succès"
            );

        } catch (\Exception $e) {
            DB::rollBack();

            // si erreur DB → supprimer le nouveau fichier uploadé
            if ($newPath && Storage::disk('public')->exists($newPath)) {
                Storage::disk('public')->delete($newPath);
            }

            return ApiResponse::error("Erreur lors de la mise à jour", $e->getMessage(), 500);
        }
    }

    /**
     * Supprimer un fichier (soft delete)
     */
    public function destroy($id)
    {
        $fichier = Fichiers::findOrFail($id);

        $fichier->delete();

        return ApiResponse::success(null, "Fichier supprimé", 200);
    }

    /**
     * Télécharger un fichier
     */
    public function download($id)
    {
        $fichier = Fichiers::findOrFail($id);
        $cheminSansStorage = str_replace('/storage/', '', $fichier->chemin);

        if (!$fichier->chemin || !file_exists(storage_path('app/public/' . $cheminSansStorage))) {
            return ApiResponse::error("Fichier introuvable sur le serveur", null, 404);
        }

        $fullPath = storage_path('app/public/' . $cheminSansStorage);

        $nomTelechargement = $fichier->titre 
            ? $fichier->titre . ($fichier->type_fichier ? '.' . $fichier->type_fichier : '')
            : basename($fichier->chemin);

        return response()->download($fullPath, $nomTelechargement);
    }

    /**
     * Validation fichiers
     */
    private function validateFichier(Request $request, $isUpdate = false)
    {
        $rules = [
            'titre'         => 'nullable|string|max:200',
            'description'   => 'nullable|string',
            'type_fichier'  => 'nullable|string|max:50',
            'chemin'        => $isUpdate ? 'sometimes|nullable|string|max:255' : 'nullable|string|max:255',
            'projet_id'     => $isUpdate ? 'sometimes|nullable|integer|exists:projets,id' : 'nullable|integer|exists:projets,id',
            'entite_type_id'=> $isUpdate ? 'sometimes|nullable|integer|exists:entites_fichier,id' : 'required|integer|exists:entites_fichier,id',
            'entite_id'     => $isUpdate ? 'sometimes|nullable|integer' : 'nullable|integer',
            'niveau_acces'  => 'nullable|integer',
            'archived'      => 'boolean',
            'fichier'       => $isUpdate ? 'sometimes|file|max:10240' : 'nullable|file|max:10240',
        ];

        return Validator::make($request->all(), $rules);
    }
}
