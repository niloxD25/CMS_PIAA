<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Projets;
use App\Models\ProjetAproposSections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProjetAproposSectionsController extends Controller
{
    /**
     * Affiche la liste des sections "À propos" d'un projet
     * GET /admin/projets/{projetId}/apropos-sections
     */
    public function index(Request $request, $projetId)
    {
        $projet = Projets::find($projetId);

        if (!$projet) {
            return redirect()
                ->route('admin.projets.index')
                ->with('error', 'Projet non trouvé');
        }

        $perPage = $request->get('per_page', 10);
        $sortBy = $request->get('sort_by', 'ordre');
        $sortOrder = $request->get('sort_order', 'asc');
        
        // Validation des paramètres de tri
        $allowedSortColumns = ['titre', 'ordre', 'created_at'];
        $allowedSortOrders = ['asc', 'desc'];
        
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'ordre';
        $sortOrder = in_array($sortOrder, $allowedSortOrders) ? $sortOrder : 'asc';
        
        $sections = ProjetAproposSections::where('projet_id', $projetId)
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
        
        // Garder tous les paramètres dans les liens de pagination
        $sections->appends([
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder
        ]);
        
        return view('admin.projets.apropos-sections.index', compact('projet', 'sections', 'sortBy', 'sortOrder'));
    }

    /**
     * Affiche le formulaire de création
     * GET /admin/projets/{projetId}/apropos-sections/create
     */
    public function create($projetId)
    {
        $projet = Projets::find($projetId);

        if (!$projet) {
            return redirect()
                ->route('admin.projets.index')
                ->with('error', 'Projet non trouvé');
        }

        return view('admin.projets.apropos-sections.create', compact('projet'));
    }

    /**
     * Enregistre une nouvelle section "À propos"
     * POST /admin/projets/{projetId}/apropos-sections
     */
    public function store(Request $request, $projetId)
    {
        $projet = Projets::find($projetId);

        if (!$projet) {
            return redirect()
                ->route('admin.projets.index')
                ->with('error', 'Projet non trouvé');
        }

        $validator = $this->validateSection($request);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        $data['projet_id'] = $projetId;

        // Gestion de l'image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('apropos-sections', 'public');
            $data['image'] = '/storage/' . $path;
        }

        // Déterminer l'ordre si non fourni
        if (!isset($data['ordre'])) {
            $maxOrdre = ProjetAproposSections::where('projet_id', $projetId)->max('ordre');
            $data['ordre'] = $maxOrdre ? $maxOrdre + 1 : 1;
        }

        try {
            DB::transaction(function () use ($data) {
                ProjetAproposSections::create($data);
            });

            return redirect()
                ->route('admin.projets.show', $projetId)
                ->with('success', 'Section "À propos" créée avec succès');

        } catch (\Exception $e) {
            // Nettoyage du fichier en cas d'erreur
            if (isset($data['image']) && Storage::disk('public')->exists(str_replace('/storage/', '', $data['image']))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $data['image']));
            }
            
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la création de la section: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Affiche les détails d'une section
     * GET /admin/projets/{projetId}/apropos-sections/{id}
     */
    public function show($projetId, $id)
    {
        $projet = Projets::find($projetId);
        $section = ProjetAproposSections::where('projet_id', $projetId)->find($id);

        if (!$projet) {
            return redirect()
                ->route('admin.projets.index')
                ->with('error', 'Projet non trouvé');
        }

        if (!$section) {
            return redirect()
                ->route('admin.projets.apropos-sections.index', $projetId)
                ->with('error', 'Section "À propos" non trouvée');
        }

        return view('admin.projets.apropos-sections.show', compact('projet', 'section'));
    }

    /**
     * Affiche le formulaire d'édition
     * GET /admin/projets/{projetId}/apropos-sections/{id}/edit
     */
    public function edit($projetId, $id)
    {
        $projet = Projets::find($projetId);
        $section = ProjetAproposSections::where('projet_id', $projetId)->find($id);

        if (!$projet) {
            return redirect()
                ->back()
                ->with('error', 'Projet non trouvé');
        }

        if (!$section) {
            return redirect()
                ->back()
                ->with('error', 'Section "À propos" non trouvée');
        }

        return view('admin.projets.apropos-sections.edit', compact('projet', 'section'));
    }

    /**
     * Met à jour une section existante
     * PUT /admin/projets/{projetId}/apropos-sections/{id}
     */
    public function update(Request $request, $projetId, $id)
    {
        $projet = Projets::find($projetId);
        $section = ProjetAproposSections::where('projet_id', $projetId)->find($id);

        if (!$projet) {
            return redirect()
                ->back()
                ->with('error', 'Projet non trouvé');
        }

        if (!$section) {
            return redirect()
                ->back()
                ->with('error', 'Section "À propos" non trouvée');
        }

        $validator = $this->validateSection($request, true);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        $oldImage = $section->image;
        $newImagePath = null;

        // Gestion de la suppression d'image
        if ($request->has('remove_image') && $request->input('remove_image') == 'on') {
            $data['image'] = null;
        }

        // Gestion de la nouvelle image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('apropos-sections', 'public');
            $newImagePath = '/storage/' . $path;
            $data['image'] = $newImagePath;
        }

        try {
            DB::transaction(function () use ($data, $section) {
                $section->update($data);
            });

            // Supprimer l'ancienne image après succès de la mise à jour
            if (($newImagePath || $request->has('remove_image')) && $oldImage && Storage::disk('public')->exists(str_replace('/storage/', '', $oldImage))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldImage));
            }

            return redirect()
                ->route('admin.projets.show', $projetId)
                ->with('success', 'Section "À propos" mise à jour avec succès');

        } catch (\Exception $e) {
            // Rollback de la nouvelle image en cas d'erreur
            if ($newImagePath && Storage::disk('public')->exists(str_replace('/storage/', '', $newImagePath))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $newImagePath));
            }

            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour de la section: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprime une section
     * DELETE /admin/projets/{projetId}/apropos-sections/{id}
     */
    public function destroy($projetId, $id)
    {
        $projet = Projets::find($projetId);
        $section = ProjetAproposSections::where('projet_id', $projetId)->find($id);

        if (!$projet) {
            return redirect()
                ->route('admin.projets.index')
                ->with('error', 'Projet non trouvé');
        }

        if (!$section) {
            return redirect()
                ->back()
                ->with('error', 'Section "À propos" non trouvée');
        }

        try {            
            DB::transaction(function () use ($section) {
                $section->delete();
            });

            return redirect()
                ->route('admin.projets.show', $projetId)
                ->with('success', 'Section "À propos" supprimée avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la suppression de la section: ' . $e->getMessage());
        }
    }

    /**
     * Validation des données de la section
     */
    private function validateSection(Request $request, $isUpdate = false)
    {
        $rules = [
            'titre' => ($isUpdate ? 'sometimes|' : '') . 'nullable|string|max:255',
            'sous_titre' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'ordre' => 'nullable|integer|min:1',
            'image' => ($isUpdate ? 'sometimes|' : 'nullable|') . 'file|mimes:jpg,jpeg,png,gif,webp,svg|max:10120',
        ];

        $messages = [
            'titre.max' => 'Le titre ne doit pas dépasser 255 caractères',
            'sous_titre.max' => 'Le sous-titre ne doit pas dépasser 255 caractères',
            'ordre.integer' => 'L\'ordre doit être un nombre entier',
            'ordre.min' => 'L\'ordre ne peut pas être inférieur à 1',
            'image.file' => 'L\'image doit être un fichier',
            'image.mimes' => 'L\'image doit être une image (jpg, jpeg, png, gif, webp, svg)',
            'image.max' => 'L\'image ne doit pas dépasser 10MB',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }
}