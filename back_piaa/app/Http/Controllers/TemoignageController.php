<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Temoignages;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TemoignageController extends Controller
{
    /**
     * Liste tous les témoignages
     */
    public function index()
    {
        $temoignages = Temoignages::with('projets')->get();
        return ApiResponse::success($temoignages, "Liste des témoignages récupérée avec succès");
    }

    /**
     * Créer un témoignage
     */
    public function store(Request $request)
    {
        $validator = $this->validateData($request);
        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        $data = $validator->validated();
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('temoignages', 'public');
            $data['image'] = '/storage/' . $path;
        }

        try {
            $temoignage = DB::transaction(fn () => Temoignages::create($data));
            return ApiResponse::success($temoignage, "Témoignage créé avec succès", 201);
        } catch (\Exception $e) {
            if (!empty($data['image']) && Storage::disk('public')->exists(str_replace('/storage/', '', $data['image']))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $data['image']));
            }
            return ApiResponse::error("Erreur lors de la création du témoignage", $e->getMessage(), 500);
        }
    }

    /**
     * Récupère un témoignage spécifique
     * GET /api/temoignages/{id}
     */
    public function show($id)
    {
        $temoignage = Temoignages::with('projets')->find($id);

        if (!$temoignage) {
            return ApiResponse::error("Témoignage non trouvé", null, 404);
        }

        return ApiResponse::success($temoignage, "Détails du Témoignage");
    }

    /**
     * Mettre à jour un témoignage
     */
    public function update(Request $request, $id)
    {
        $temoignage = Temoignages::find($id);
        if (!$temoignage) return ApiResponse::error("Témoignage non trouvé", null, 404);

        $validator = $this->validateData($request, true);
        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        $data = $validator->validated();
        $oldImage = $temoignage->image;
        $newImagePath = null;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('temoignages', 'public');
            $newImagePath = '/storage/' . $path;
            $data['image'] = $newImagePath;
        }

        try {
            DB::transaction(fn () => $temoignage->update($data));

            if ($newImagePath && $oldImage && Storage::disk('public')->exists(str_replace('/storage/', '', $oldImage))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldImage));
            }

            return ApiResponse::success($temoignage->fresh(), "Témoignage mis à jour avec succès");
        } catch (\Exception $e) {
            if ($newImagePath && Storage::disk('public')->exists(str_replace('/storage/', '', $newImagePath))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $newImagePath));
            }
            return ApiResponse::error("Erreur lors de la mise à jour du témoignage", $e->getMessage(), 500);
        }
    }

    /**
     * Supprimer un témoignage
     */
    public function destroy($id)
    {
        $temoignage = Temoignages::find($id);
        if (!$temoignage) return ApiResponse::error("Témoignage non trouvé", null, 404);

        try {
            $temoignage->delete();

            return ApiResponse::success(null, "Témoignage supprimé avec succès");
        } catch (\Exception $e) {
            return ApiResponse::error("Erreur lors de la suppression du témoignage", $e->getMessage(), 500);
        }
    }

    /**
     * Validation
     */
    private function validateData(Request $request, $isUpdate = false)
    {
        $rules = [
            'temoin' => ($isUpdate ? 'sometimes|' : 'required|') . 'string|max:255',
            'residence' => ($isUpdate ? 'sometimes|' : 'nullable|') . 'string|max:255',
            'contenu' => ($isUpdate ? 'sometimes|' : 'nullable|') . 'string',
            'image' => $isUpdate ? 'sometimes|file|mimes:jpg,jpeg,png|max:2048' : 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'projet_id' => ($isUpdate ? 'sometimes|' : 'nullable|') . 'integer|exists:projets,id',
            'archived' => ($isUpdate ? 'sometimes|' : 'nullable|') . 'boolean'
        ];

        return Validator::make($request->all(), $rules);
    }
}
