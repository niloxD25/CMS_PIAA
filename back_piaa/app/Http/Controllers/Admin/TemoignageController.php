<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Temoignages;
use App\Models\Projets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TemoignageController extends Controller
{
    /**
     * Affiche la liste des témoignages
     * GET /admin/temoignages
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $sortBy = $request->get('sort_by', 'temoin');
        $sortOrder = $request->get('sort_order', 'asc');
        
        $allowedSortColumns = ['temoin', 'residence', 'projet'];
        $allowedSortOrders = ['asc', 'desc'];
        
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'temoin';
        $sortOrder = in_array($sortOrder, $allowedSortOrders) ? $sortOrder : 'asc';
        
        $query = Temoignages::with('projets');
        
        $filters = $request->only(['projet_id', 'archived', 'est_important']);
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $query->where("temoignages.$key", $value);
            }
        }
        
        if ($sortBy === 'projet') {
            $query->leftJoin('projets', 'temoignages.projet_id', '=', 'projets.id')
                ->select('temoignages.*')
                ->orderBy('projets.nom', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }
        
        $temoignages = $query->paginate($perPage);

        $projets = Projets::all();
        
        $temoignages->appends([
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder
        ] + $filters);
        
        return view('admin.temoignages.index', compact('temoignages', 'sortBy', 'sortOrder', 'projets'));
    }

    /**
     * Affiche le formulaire de création
     * GET /admin/temoignages/create
     */
    public function create()
    {
        $projets = Projets::all();
        return view('admin.temoignages.create', compact('projets'));
    }

    /**
     * Enregistre un nouveau témoignage
     * POST /admin/temoignages
     */
    public function store(Request $request)
    {
        $validator = $this->validateTemoignage($request);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Gestion de l'image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('temoignages', 'public');
            $data['image'] = '/storage/' . $path;
        }

        try {
            DB::transaction(function () use ($data) {
                Temoignages::create($data);
            });

            return redirect()
                ->route('admin.temoignages.index')
                ->with('success', 'Témoignage créé avec succès');

        } catch (\Exception $e) {
            // Nettoyage du fichier en cas d'erreur
            if (isset($data['image']) && Storage::disk('public')->exists(str_replace('/storage/', '', $data['image']))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $data['image']));
            }
            
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la création du témoignage: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Affiche les détails d'un témoignage
     * GET /admin/temoignages/{id}
     */
    public function show($id)
    {
        $temoignage = Temoignages::with('projets')->find($id);

        if (!$temoignage) {
            return redirect()
                ->route('admin.temoignages.index')
                ->with('error', 'Témoignage non trouvé');
        }

        return view('admin.temoignages.show', compact('temoignage'));
    }

    /**
     * Affiche le formulaire d'édition
     * GET /admin/temoignages/{id}/edit
     */
    public function edit($id)
    {
        $temoignage = Temoignages::find($id);
        $projets = Projets::all();

        if (!$temoignage) {
            return redirect()
                ->route('admin.temoignages.index')
                ->with('error', 'Témoignage non trouvé');
        }

        return view('admin.temoignages.edit', compact('temoignage', 'projets'));
    }

    /**
     * Met à jour un témoignage existant
     * PUT /admin/temoignages/{id}
     */
    public function update(Request $request, $id)
    {
        $temoignage = Temoignages::find($id);

        if (!$temoignage) {
            return redirect()
                ->route('admin.temoignages.index')
                ->with('error', 'Témoignage non trouvé');
        }

        $validator = $this->validateTemoignage($request, true);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        $oldImage = $temoignage->image;
        $newImagePath = null;

        // Gestion de la nouvelle image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('temoignages', 'public');
            $newImagePath = '/storage/' . $path;
            $data['image'] = $newImagePath;
        }

        try {
            DB::transaction(function () use ($data, $temoignage) {
                $temoignage->update($data);
            });

            // Supprimer l'ancienne image après succès de la mise à jour
            if ($newImagePath && $oldImage && Storage::disk('public')->exists(str_replace('/storage/', '', $oldImage))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldImage));
            }

            return redirect()
                ->route('admin.temoignages.index')
                ->with('success', 'Témoignage mis à jour avec succès');

        } catch (\Exception $e) {
            // Rollback de la nouvelle image en cas d'erreur
            if ($newImagePath && Storage::disk('public')->exists(str_replace('/storage/', '', $newImagePath))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $newImagePath));
            }

            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour du témoignage: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprime un témoignage
     * DELETE /admin/temoignages/{id}
     */
    public function destroy($id)
    {
        $temoignage = Temoignages::find($id);

        if (!$temoignage) {
            return redirect()
                ->route('admin.temoignages.index')
                ->with('error', 'Témoignage non trouvé');
        }

        try {            
            DB::transaction(function () use ($temoignage) {
                $temoignage->delete();
            });

            return redirect()
                ->route('admin.temoignages.index')
                ->with('success', 'Témoignage supprimé avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.temoignages.index')
                ->with('error', 'Erreur lors de la suppression du témoignage: ' . $e->getMessage());
        }
    }

    /**
     * Archive/Désarchive un témoignage
     * PATCH /admin/temoignages/{id}/toggle-archive
     */
    public function toggleArchive($id)
    {
        $temoignage = Temoignages::find($id);

        if (!$temoignage) {
            return redirect()
                ->route('admin.temoignages.index')
                ->with('error', 'Témoignage non trouvé');
        }

        try {
            $temoignage->update(['archived' => !$temoignage->archived]);
            
            $action = $temoignage->archived ? 'archivé' : 'désarchivé';
            
            return redirect()
                ->route('admin.temoignages.index')
                ->with('success', "Témoignage {$action} avec succès");

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.temoignages.index')
                ->with('error', 'Erreur lors de l\'archivage du témoignage: ' . $e->getMessage());
        }
    }

    /**
     * Basculer l'état important d'un témoignage
     * PATCH /admin/temoignages/{id}/toggle-important
     */
    public function toggleImportant($id)
    {
        $temoignage = Temoignages::find($id);

        if (!$temoignage) {
            return redirect()
                ->route('admin.temoignages.index')
                ->with('error', 'Témoignage non trouvé');
        }

        try {
            $temoignage->update(['est_important' => !$temoignage->est_important]);
            
            $action = $temoignage->est_important ? 'marqué comme important' : 'retiré des importants';
            
            return redirect()
                ->route('admin.temoignages.index')
                ->with('success', "Témoignage {$action} avec succès");

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.temoignages.index')
                ->with('error', 'Erreur lors du changement d\'état important: ' . $e->getMessage());
        }
    }

    /* ==========
       Validation
    ============= */
    private function validateTemoignage(Request $request, $isUpdate = false)
    {
        $rules = [
            'temoin' => ($isUpdate ? 'sometimes|' : 'required|') . 'string|max:255',
            'residence' => 'nullable|string|max:255',
            'contenu' => ($isUpdate ? 'sometimes|' : 'required|') . 'string',
            'image' => ($isUpdate ? 'sometimes|' : 'nullable|') . 'file|mimes:jpg,jpeg,png|max:10048',
            'projet_id' => 'nullable|integer|exists:projets,id',
            'archived' => 'nullable|boolean',
            'est_important' => 'nullable|boolean'
        ];

        $messages = [
            'temoin.required' => 'Le nom du témoin est obligatoire',
            'temoin.max' => 'Le nom du témoin ne doit pas dépasser 255 caractères',
            'residence.max' => 'La résidence ne doit pas dépasser 255 caractères',
            'contenu.required' => 'Le contenu du témoignage est obligatoire',
            'image.file' => 'L\'image doit être un fichier',
            'image.mimes' => 'L\'image doit être au format JPG, JPEG ou PNG',
            'image.max' => 'L\'image ne doit pas dépasser 10MB',
            'projet_id.exists' => 'Le projet sélectionné n\'existe pas',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }
}