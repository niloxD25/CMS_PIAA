<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Realisations;
use App\Models\Projets;
use App\Models\TypesCompte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RealisationController extends Controller
{
    /**
     * Affiche la liste des réalisations
     * GET /admin/realisations
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $sortBy = $request->get('sort_by', 'date_realisation');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSortColumns = ['titre', 'date_realisation', 'projet'];
        $allowedSortOrders = ['asc', 'desc'];

        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'date_realisation';
        $sortOrder = in_array($sortOrder, $allowedSortOrders) ? $sortOrder : 'desc';

        $query = Realisations::with(['projets']);

        // Appliquer seulement les filtres remplis
        $filters = $request->only(['projet_id', 'archived']);
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $query->where("realisations.$key", $value);
            }
        }

        // Tri
        if ($sortBy === 'projet') {
            $query->leftJoin('projets', 'realisations.projet_id', '=', 'projets.id')
                ->select('realisations.*')
                ->orderBy('projets.nom', $sortOrder);
        } else {
            $query->orderBy("realisations.$sortBy", $sortOrder);
        }

        $realisations = $query->paginate($perPage);
        $projets = Projets::all();

        $realisations->appends([
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
        ] + $filters);

        return view('admin.realisations.index', compact('realisations', 'projets', 'sortBy', 'sortOrder'));
    }

    /**
     * Affiche le formulaire de création
     * GET /admin/realisations/create
     */
    public function create()
    {
        $projets = Projets::all();
        $typesCompte = TypesCompte::orderBy('niveau_acces')->get();
        
        return view('admin.realisations.create', compact('projets', 'typesCompte'));
    }

    /**
     * Enregistre une nouvelle réalisation
     * POST /admin/realisations
     */
    public function store(Request $request)
    {
        $validator = $this->validateRealisation($request);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Gestion de l'image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('realisations', 'public');
            $data['image'] = '/storage/' . $path;
        }

        try {
            DB::transaction(function () use ($data) {
                Realisations::create($data);
            });

            return redirect()
                ->route('admin.realisations.index')
                ->with('success', 'Réalisation créée avec succès');

        } catch (\Exception $e) {
            // Nettoyage du fichier en cas d'erreur
            if (isset($data['image']) && Storage::disk('public')->exists(str_replace('/storage/', '', $data['image']))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $data['image']));
            }
            
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la création de la réalisation: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Affiche les détails d'une réalisation
     * GET /admin/realisations/{id}
     */
    public function show($id)
    {
        $realisation = Realisations::with(['projets'])->find($id);

        if (!$realisation) {
            return redirect()
                ->route('admin.realisations.index')
                ->with('error', 'Réalisation non trouvée');
        }

        return view('admin.realisations.show', compact('realisation'));
    }

    /**
     * Affiche le formulaire d'édition
     * GET /admin/realisations/{id}/edit
     */
    public function edit($id)
    {
        $realisation = Realisations::find($id);
        $projets = Projets::all();
        $typesCompte = TypesCompte::orderBy('niveau_acces')->get();

        if (!$realisation) {
            return redirect()
                ->route('admin.realisations.index')
                ->with('error', 'Réalisation non trouvée');
        }

        return view('admin.realisations.edit', compact('realisation', 'projets', 'typesCompte'));
    }

    /**
     * Met à jour une réalisation existante
     * PUT /admin/realisations/{id}
     */
    public function update(Request $request, $id)
    {
        $realisation = Realisations::find($id);

        if (!$realisation) {
            return redirect()
                ->route('admin.realisations.index')
                ->with('error', 'Réalisation non trouvée');
        }

        $validator = $this->validateRealisation($request, true);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        $oldImage = $realisation->image;
        $newImagePath = null;

        // Gestion de la nouvelle image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('realisations', 'public');
            $newImagePath = '/storage/' . $path;
            $data['image'] = $newImagePath;
        }

        try {
            DB::transaction(function () use ($data, $realisation) {
                $realisation->update($data);
            });

            // Supprimer l'ancienne image après succès de la mise à jour
            if ($newImagePath && $oldImage && Storage::disk('public')->exists(str_replace('/storage/', '', $oldImage))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldImage));
            }

            return redirect()
                ->route('admin.realisations.index')
                ->with('success', 'Réalisation mise à jour avec succès');

        } catch (\Exception $e) {
            // Rollback de la nouvelle image en cas d'erreur
            if ($newImagePath && Storage::disk('public')->exists(str_replace('/storage/', '', $newImagePath))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $newImagePath));
            }

            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour de la réalisation: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprime une réalisation
     * DELETE /admin/realisations/{id}
     */
    public function destroy($id)
    {
        $realisation = Realisations::find($id);

        if (!$realisation) {
            return redirect()
                ->route('admin.realisations.index')
                ->with('error', 'Réalisation non trouvée');
        }

        try {            
            DB::transaction(function () use ($realisation) {
                $realisation->delete();
            });

            return redirect()
                ->route('admin.realisations.index')
                ->with('success', 'Réalisation supprimée avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.realisations.index')
                ->with('error', 'Erreur lors de la suppression de la réalisation: ' . $e->getMessage());
        }
    }

    /**
     * Archive/Désarchive une réalisation
     * PATCH /admin/realisations/{id}/toggle-archive
     */
    public function toggleArchive($id)
    {
        $realisation = Realisations::find($id);

        if (!$realisation) {
            return redirect()
                ->route('admin.realisations.index')
                ->with('error', 'Réalisation non trouvée');
        }

        try {
            $realisation->update(['archived' => !$realisation->archived]);
            
            $action = $realisation->archived ? 'archivée' : 'désarchivée';
            
            return redirect()
                ->route('admin.realisations.index')
                ->with('success', "Réalisation {$action} avec succès");

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.realisations.index')
                ->with('error', 'Erreur lors de l\'archivage de la réalisation: ' . $e->getMessage());
        }
    }

    /* ==========
       Validation
    ============= */

    private function validateRealisation(Request $request, $isUpdate = false)
    {
        $rules = [
            'titre' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'beneficiaires' => 'nullable|string',
            'impacts_potentiel' => 'nullable|string',
            'date_realisation' => 'nullable|date',
            'projet_id' => 'integer|exists:projets,id',
            'niveau_acces' => ($isUpdate ? 'sometimes|' : 'required|') . 'integer|exists:types_compte,niveau_acces',
            'archived' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10048'
        ];

        $messages = [
            'titre.max' => 'Le titre ne doit pas dépasser 200 caractères',
            'projet_id.exists' => 'Le projet sélectionné n\'existe pas',
            'niveau_acces.required' => 'Le niveau d\'accès est obligatoire',
            'niveau_acces.exists' => 'Le niveau d\'accès sélectionné n\'existe pas',
            'image.image' => 'Le fichier doit être une image',
            'image.mimes' => 'L\'image doit être au format JPEG, PNG, JPG ou GIF',
            'image.max' => 'L\'image ne doit pas dépasser 10MB',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }
}