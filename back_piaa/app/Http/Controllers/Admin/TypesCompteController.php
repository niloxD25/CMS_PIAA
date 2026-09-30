<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TypesCompte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TypesCompteController extends Controller
{
    /**
     * Affiche la liste des types de compte
     * GET /admin/users/types-compte
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $sortBy = $request->get('sort_by', 'niveau_acces');
        $sortOrder = $request->get('sort_order', 'asc');

        $allowedSortColumns = ['type', 'niveau_acces'];
        $allowedSortOrders = ['asc', 'desc'];

        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'niveau_acces';
        $sortOrder = in_array($sortOrder, $allowedSortOrders) ? $sortOrder : 'asc';

        $query = TypesCompte::withCount(['users']);

        if (auth()->user()->type_compte_id == 2) {
            $query->whereNotIn('id', [1, 2]);
        }

        // Recherche par type
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->whereRaw('LOWER(type) LIKE ?', ['%' . strtolower($search) . '%']);
        }
        // Tri
        $query->orderBy("types_compte.$sortBy", $sortOrder);

        $typesCompte = $query->paginate($perPage);

        $typesCompte->appends([
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
            'search' => $request->search,
        ]);

        return view('admin.users.types-compte.index', compact('typesCompte', 'sortBy', 'sortOrder'));
    }

    /**
     * Affiche le formulaire de création
     * GET /admin/types-compte/create
     */
    public function create()
    {
        return view('admin.users.types-compte.create');
    }

    /**
     * Enregistre un nouveau type de compte
     * POST /admin/types-compte
     */
    public function store(Request $request)
    {
        $validator = $this->validateTypesCompte($request);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        try {
            DB::transaction(function () use ($data) {
                TypesCompte::create($data);
            });

            return redirect()
                ->route('admin.users.types-compte.index')
                ->with('success', 'Type de compte créé avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la création du type de compte: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Affiche les détails d'un type de compte
     * GET /admin/types-compte/{id}
     */
    public function show($id)
    {
        $typeCompte = TypesCompte::with(['users'])->withCount(['users'])->find($id);

        if (!$typeCompte) {
            return redirect()
                ->route('admin.users.types-compte.index')
                ->with('error', 'Type de compte non trouvé');
        }

        return view('admin.users.types-compte.show', compact('typeCompte'));
    }

    /**
     * Affiche le formulaire d'édition
     * GET /admin/types-compte/{id}/edit
     */
    public function edit($id)
    {
        $typeCompte = TypesCompte::withCount(['users'])->find($id);

        if (!$typeCompte) {
            return redirect()
                ->route('admin.users.types-compte.index')
                ->with('error', 'Type de compte non trouvé');
        }

        return view('admin.users.types-compte.edit', compact('typeCompte'));
    }

    /**
     * Met à jour un type de compte existant
     * PUT /admin/types-compte/{id}
     */
    public function update(Request $request, $id)
    {
        $typeCompte = TypesCompte::find($id);

        if (!$typeCompte) {
            return redirect()
                ->route('admin.users.types-compte.index')
                ->with('error', 'Type de compte non trouvé');
        }

        $validator = $this->validateTypesCompte($request, true, $typeCompte->id);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        try {
            DB::transaction(function () use ($data, $typeCompte) {
                $typeCompte->update($data);
            });

            return redirect()
                ->route('admin.users.types-compte.index')
                ->with('success', 'Type de compte mis à jour avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour du type de compte: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprime un type de compte
     * DELETE /admin/types-compte/{id}
     */
    public function destroy($id)
    {
        $typeCompte = TypesCompte::withCount(['users'])->find($id);

        if (!$typeCompte) {
            return redirect()
                ->route('admin.users.types-compte.index')
                ->with('error', 'Type de compte non trouvé');
        }

        // Vérifier s'il y a des utilisateurs associés
        if ($typeCompte->users_count > 0) {
            return redirect()
                ->route('admin.users.types-compte.index')
                ->with('error', 'Impossible de supprimer ce type de compte car il est associé à ' . $typeCompte->users_count . ' utilisateur(s)');
        }

        try {            
            DB::transaction(function () use ($typeCompte) {
                $typeCompte->delete();
            });

            return redirect()
                ->route('admin.users.types-compte.index')
                ->with('success', 'Type de compte supprimé avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.users.types-compte.index')
                ->with('error', 'Erreur lors de la suppression du type de compte: ' . $e->getMessage());
        }
    }

    /* ==========
       Validation
    ============= */

    private function validateTypesCompte(Request $request, $isUpdate = false, $typeCompteId = null)
    {
        $rules = [
            'type' => 'required|string|max:100',
            'niveau_acces' => 'required|integer|min:1|max:999',
        ];

        // Règles pour le type (unique)
        if ($isUpdate) {
            $rules['type'] .= '|unique:types_compte,type,' . $typeCompteId;
            $rules['niveau_acces'] .= '|unique:types_compte,niveau_acces,' . $typeCompteId;
        } else {
            $rules['type'] .= '|unique:types_compte,type';
            $rules['niveau_acces'] .= '|unique:types_compte,niveau_acces';
        }

        $messages = [
            'type.required' => 'Le type est obligatoire',
            'type.max' => 'Le type ne doit pas dépasser 100 caractères',
            'type.unique' => 'Ce type de compte existe déjà',
            'niveau_acces.required' => 'Le niveau d\'accès est obligatoire',
            'niveau_acces.integer' => 'Le niveau d\'accès doit être un nombre entier',
            'niveau_acces.min' => 'Le niveau d\'accès doit être au moins 1',
            'niveau_acces.max' => 'Le niveau d\'accès ne peut pas dépasser 999',
            'niveau_acces.unique' => 'Ce niveau d\'accès est déjà utilisé',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }
}