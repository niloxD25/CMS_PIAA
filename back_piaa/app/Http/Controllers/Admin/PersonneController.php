<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Personnes;
use App\Models\Projets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PersonneController extends Controller
{
    /**
     * Affiche la liste des personnes
     * GET /admin/personnes
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $sortBy = $request->get('sort_by', 'nom');
        $sortOrder = $request->get('sort_order', 'asc');
        
        // Validation des paramètres de tri
        $allowedSortColumns = ['nom', 'prenom', 'poste'];
        $allowedSortOrders = ['asc', 'desc'];
        
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'nom';
        $sortOrder = in_array($sortOrder, $allowedSortOrders) ? $sortOrder : 'asc';
        
        $query = Personnes::withCount('projets');
        
        // Filtres
        $filters = $request->only(['nom', 'prenom', 'poste']);
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $query->where($key, 'ILIKE', '%' . $value . '%');
            }
        }
        
        $personnes = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);
        
        // Garder tous les paramètres dans les liens de pagination
        $personnes->appends([
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder
        ] + $filters);
        
        return view('admin.personnes.index', compact('personnes', 'sortBy', 'sortOrder'));
    }

    /**
     * Affiche le formulaire de création
     * GET /admin/personnes/create
     */
    public function create()
    {
        return view('admin.personnes.create');
    }

    /**
     * Enregistre une nouvelle personne
     * POST /admin/personnes
     */
    public function store(Request $request)
    {
        $validator = $this->validateData($request);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Gestion de la photo
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('personnes', 'public');
            $data['photo'] = '/storage/' . $path;
        }

        try {
            DB::transaction(function () use ($data) {
                Personnes::create($data);
            });

            return redirect()
                ->route('admin.personnes.index')
                ->with('success', 'Personnes créée avec succès');

        } catch (\Exception $e) {
            // Nettoyage de la photo en cas d'erreur
            if (isset($data['photo']) && Storage::disk('public')->exists(str_replace('/storage/', '', $data['photo']))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $data['photo']));
            }
            
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la création de la personne: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Affiche les détails d'une personne
     * GET /admin/personnes/{id}
     */
    public function show($id)
    {
        $personne = Personnes::with(['projet_personnes' => function($query) {
            $query->with('projets')
                ->orderBy('hierarchie')
                ->orderBy('date_debut_participation');
        }])->find($id);
        
        if (!$personne) {
            return redirect()
                ->route('admin.personnes.index')
                ->with('error', 'Personne non trouvée');
        }

        // Récupérer les IDs des projets déjà associés
        $projetIds = $personne->projet_personnes->pluck('projet_id')->toArray();
        
        // Récupérer les projets non associés
        $projets = Projets::whereNotIn('id', $projetIds)->get();

        return view('admin.personnes.show', compact('personne', 'projets'));
    }

    /**
     * Affiche le formulaire d'édition
     * GET /admin/personnes/{id}/edit
     */
    public function edit($id)
    {
        $personne = Personnes::find($id);

        if (!$personne) {
            return redirect()
                ->route('admin.personnes.index')
                ->with('error', 'Personnes non trouvée');
        }

        return view('admin.personnes.edit', compact('personne'));
    }

    /**
     * Met à jour une personne existante
     * PUT /admin/personnes/{id}
     */
    public function update(Request $request, $id)
    {
        $personne = Personnes::find($id);

        if (!$personne) {
            return redirect()
                ->route('admin.personnes.index')
                ->with('error', 'Personne non trouvée');
        }

        $validator = $this->validateData($request, true);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        $oldPhoto = $personne->photo;
        $newPhotoPath = null;

        // Gestion de la nouvelle photo
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('personnes', 'public');
            $newPhotoPath = '/storage/' . $path;
            $data['photo'] = $newPhotoPath;
        }

        try {
            DB::transaction(function () use ($personne, $data) {
                $personne->update($data);
            });

            // Supprimer l'ancienne photo après succès de la mise à jour
            if ($newPhotoPath && $oldPhoto && Storage::disk('public')->exists(str_replace('/storage/', '', $oldPhoto))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldPhoto));
            }

            return redirect()
                ->route('admin.personnes.index')
                ->with('success', 'Personne mise à jour avec succès');

        } catch (\Exception $e) {
            // Rollback de la nouvelle photo en cas d'erreur
            if ($newPhotoPath && Storage::disk('public')->exists(str_replace('/storage/', '', $newPhotoPath))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $newPhotoPath));
            }

            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour de la personne: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprime une personne
     * DELETE /admin/personnes/{id}
     */
    public function destroy($id)
    {
        $personne = Personnes::find($id);

        if (!$personne) {
            return redirect()
                ->route('admin.personnes.index')
                ->with('error', 'Personne non trouvée');
        }

        try {            
            DB::transaction(function () use ($personne) {
                $personne->delete();
            });

            return redirect()
                ->route('admin.personnes.index')
                ->with('success', 'Personnes supprimée avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.personnes.index')
                ->with('error', 'Erreur lors de la suppression de la personne: ' . $e->getMessage());
        }
    }

    /**
     * Validation des données de la personne
     */
    private function validateData(Request $request, $isUpdate = false)
    {
        $rules = [
            'nom' => ($isUpdate ? 'sometimes|' : 'required|') . 'string|max:100',
            'prenom' => ($isUpdate ? 'sometimes|' : 'nullable|') . 'string|max:100',
            'poste' => 'nullable|string|max:250',
            'photo' => ($isUpdate ? 'sometimes|' : 'nullable|') . 'file|mimes:jpg,jpeg,png,gif|max:10048',
        ];

        $messages = [
            'nom.required' => 'Le nom est obligatoire',
            'nom.max' => 'Le nom ne doit pas dépasser 100 caractères',
            'prenom.max' => 'Le prénom ne doit pas dépasser 100 caractères',
            'poste.max' => 'Le poste ne doit pas dépasser 250 caractères',
            'photo.file' => 'La photo doit être un fichier',
            'photo.mimes' => 'La photo doit être au format JPG, JPEG, PNG ou GIF',
            'photo.max' => 'La photo ne doit pas dépasser 10MB',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }
}