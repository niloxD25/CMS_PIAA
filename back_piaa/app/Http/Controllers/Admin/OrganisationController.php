<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organisations;
use App\Models\OrganisationProjet;
use App\Models\TypesRelation;
use App\Models\Projets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class OrganisationController extends Controller
{
    /* =======================
       Gestion des Organisations
    ======================= */

    /**
     * Affiche la liste des organisations
     * GET /admin/organisations
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $sortBy = $request->get('sort_by', 'nom');
        $sortOrder = $request->get('sort_order', 'asc');
        
        $allowedSortColumns = ['nom', 'lien'];
        $allowedSortOrders = ['asc', 'desc'];
        
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'nom';
        $sortOrder = in_array($sortOrder, $allowedSortOrders) ? $sortOrder : 'asc';
        
        $organisations = Organisations::orderBy($sortBy, $sortOrder)->paginate($perPage);
        
        $organisations->appends([
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder
        ]);
        
        return view('admin.organisations.index', compact('organisations', 'sortBy', 'sortOrder'));
    }

    /**
     * Affiche le formulaire de création
     * GET /admin/organisations/create
     */
    public function create()
    {
        return view('admin.organisations.create');
    }

    /**
     * Enregistre une nouvelle organisation
     * POST /admin/organisations
     */
    public function store(Request $request)
    {
        $validator = $this->validateOrganisation($request);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Gestion du logo
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('organisations', 'public');
            $data['logo'] = '/storage/' . $path;
        }

        try {
            DB::transaction(function () use ($data) {
                Organisations::create($data);
            });

            return redirect()
                ->route('admin.organisations.index')
                ->with('success', 'Organisation créée avec succès');

        } catch (\Exception $e) {
            // Nettoyage du fichier en cas d'erreur
            if (isset($data['logo']) && Storage::disk('public')->exists(str_replace('/storage/', '', $data['logo']))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $data['logo']));
            }
            
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la création de l\'organisation: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Affiche les détails d'une organisation
     * GET /admin/organisations/{id}
     */
    public function show($id)
    {
        $organisation = Organisations::find($id);

        if (!$organisation) {
            return redirect()
                ->route('admin.organisations.index')
                ->with('error', 'Organisation non trouvée');
        }

        // Récupérer les associations de l'organisation
        $associations = OrganisationProjet::with(['projets', 'types_relation'])
            ->where('organisation_id', $id)
            ->get();

        return view('admin.organisations.show', compact('organisation', 'associations'));
    }

    /**
     * Affiche le formulaire d'édition
     * GET /admin/organisations/{id}/edit
     */
    public function edit($id)
    {
        $organisation = Organisations::find($id);

        if (!$organisation) {
            return redirect()
                ->route('admin.organisations.index')
                ->with('error', 'Organisation non trouvée');
        }

        return view('admin.organisations.edit', compact('organisation'));
    }

    /**
     * Met à jour une organisation existante
     * PUT /admin/organisations/{id}
     */
    public function update(Request $request, $id)
    {
        $organisation = Organisations::find($id);

        if (!$organisation) {
            return redirect()
                ->route('admin.organisations.index')
                ->with('error', 'Organisation non trouvée');
        }

        $validator = $this->validateOrganisation($request, true);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        $oldLogo = $organisation->logo;
        $newLogoPath = null;

        // Gestion du nouveau logo
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('organisations', 'public');
            $newLogoPath = '/storage/' . $path;
            $data['logo'] = $newLogoPath;
        }

        try {
            DB::transaction(function () use ($data, $organisation) {
                $organisation->update($data);
            });

            // Supprimer l'ancien logo après succès de la mise à jour
            if ($newLogoPath && $oldLogo && Storage::disk('public')->exists(str_replace('/storage/', '', $oldLogo))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldLogo));
            }

            return redirect()
                ->route('admin.organisations.index')
                ->with('success', 'Organisation mise à jour avec succès');

        } catch (\Exception $e) {
            // Rollback du nouveau logo en cas d'erreur
            if ($newLogoPath && Storage::disk('public')->exists(str_replace('/storage/', '', $newLogoPath))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $newLogoPath));
            }

            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour de l\'organisation: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprime une organisation
     * DELETE /admin/organisations/{id}
     */
    public function destroy($id)
    {
        $organisation = Organisations::find($id);

        if (!$organisation) {
            return redirect()
                ->route('admin.organisations.index')
                ->with('error', 'Organisation non trouvée');
        }

        try {            
            DB::transaction(function () use ($organisation) {
                $organisation->delete();
            });

            return redirect()
                ->route('admin.organisations.index')
                ->with('success', 'Organisation supprimée avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.organisations.index')
                ->with('error', 'Erreur lors de la suppression de l\'organisation: ' . $e->getMessage());
        }
    }

    /* =======================
       Gestion des Associations
    ======================= */

    /**
     * Affiche la liste des associations
     * GET /admin/organisations/associations
     */
    public function indexAssociations(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        
        $query = OrganisationProjet::with([
            'organisations',
            'projets',
            'types_relation'
        ]);

        // Filtres
        if ($request->has('projet_id') && $request->projet_id) {
            $query->where('projet_id', $request->projet_id);
        }

        if ($request->has('type_relation_id') && $request->type_relation_id) {
            $query->where('type_relation_id', $request->type_relation_id);
        }

        if ($request->has('organisation_id') && $request->organisation_id) {
            $query->where('organisation_id', $request->organisation_id);
        }

        $associations = $query->paginate($perPage);
        $organisations = Organisations::all();
        $projets = Projets::all();
        $typesRelation = TypesRelation::all();

        $associations->appends([
            'per_page' => $perPage,
            'projet_id' => $request->projet_id,
            'type_relation_id' => $request->type_relation_id,
            'organisation_id' => $request->organisation_id
        ]);

        return view('admin.organisations.associations.index', 
            compact('associations', 'organisations', 'projets', 'typesRelation'));
    }

    /**
     * Affiche le formulaire de création d'association
     * GET /admin/organisations/associations/create
     */
    public function createAssociation()
    {
        $organisations = Organisations::all();
        $projets = Projets::all();
        $typesRelation = TypesRelation::all();

        return view('admin.organisations.associations.create', 
            compact('organisations', 'projets', 'typesRelation'));
    }

    /**
     * Enregistre une nouvelle association
     * POST /admin/organisations/associations
     */
    public function storeAssociation(Request $request)
    {
        $validator = $this->validateAssociation($request);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::transaction(function () use ($validator) {
                OrganisationProjet::create($validator->validated());
            });

            return redirect()
                ->route('admin.organisations.associations.index')
                ->with('success', 'Association créée avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la création de l\'association: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Affiche les détails d'une association
     * GET /admin/organisations/associations/{id}
     */
    public function showAssociation($id)
    {
        $association = OrganisationProjet::with(['organisations', 'projets', 'types_relation'])->find($id);

        if (!$association) {
            return redirect()
                ->route('admin.organisations.associations.index')
                ->with('error', 'Association non trouvée');
        }

        return view('admin.organisations.associations.show', compact('association'));
    }

    /**
     * Affiche le formulaire d'édition d'association
     * GET /admin/organisations/associations/{id}/edit
     */
    public function editAssociation($id)
    {
        $association = OrganisationProjet::find($id);

        if (!$association) {
            return redirect()
                ->route('admin.organisations.associations.index')
                ->with('error', 'Association non trouvée');
        }

        $organisations = Organisations::all();
        $projets = Projets::all();
        $typesRelation = TypesRelation::all();

        return view('admin.organisations.associations.edit', 
            compact('association', 'organisations', 'projets', 'typesRelation'));
    }

    /**
     * Met à jour une association
     * PUT /admin/organisations/associations/{id}
     */
    public function updateAssociation(Request $request, $id)
    {
        $association = OrganisationProjet::find($id);

        if (!$association) {
            return redirect()
                ->route('admin.organisations.associations.index')
                ->with('error', 'Association non trouvée');
        }

        $validator = $this->validateAssociation($request, $id);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::transaction(function () use ($association, $validator) {
                $association->update($validator->validated());
            });

            return redirect()
                ->route('admin.organisations.associations.index')
                ->with('success', 'Association mise à jour avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour de l\'association: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprime une association
     * DELETE /admin/organisations/associations/{id}
     */
    public function destroyAssociation($id)
    {
        $association = OrganisationProjet::find($id);

        if (!$association) {
            return redirect()
                ->route('admin.organisations.associations.index')
                ->with('error', 'Association non trouvée');
        }

        try {            
            DB::transaction(function () use ($association) {
                $association->delete();
            });

            return redirect()
                ->route('admin.organisations.associations.index')
                ->with('success', 'Association supprimée avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.organisations.associations.index')
                ->with('error', 'Erreur lors de la suppression de l\'association: ' . $e->getMessage());
        }
    }

    /* =======================
       Gestion des Types de Relation
    ======================= */

    /**
     * Affiche la liste des types de relation
     * GET /admin/organisations/types-relation
     */
    public function indexTypesRelation()
    {
        $types = TypesRelation::all();
        return view('admin.organisations.types-relation.index', compact('types'));
    }

    /**
     * Affiche le formulaire de création d'un type de relation
     * GET /admin/organisations/types-relation/create
     */
    public function createTypeRelation()
    {
        return view('admin.organisations.types-relation.create');
    }

    /**
     * Enregistre un nouveau type de relation
     * POST /admin/organisations/types-relation
     */
    public function storeTypeRelation(Request $request)
    {
        $validator = $this->validateTypeRelation($request);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::transaction(function () use ($validator) {
                TypesRelation::create($validator->validated());
            });

            return redirect()
                ->route('admin.organisations.types-relation.index')
                ->with('success', 'Type de relation créé avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la création du type de relation: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Affiche le formulaire d'édition d'un type de relation
     * GET /admin/organisations/types-relation/{id}/edit
     */
    public function editTypeRelation($id)
    {
        $type = TypesRelation::find($id);

        if (!$type) {
            return redirect()
                ->route('admin.organisations.types-relation.index')
                ->with('error', 'Type de relation non trouvé');
        }

        return view('admin.organisations.types-relation.edit', compact('type'));
    }

    /**
     * Met à jour un type de relation
     * PUT /admin/organisations/types-relation/{id}
     */
    public function updateTypeRelation(Request $request, $id)
    {
        $type = TypesRelation::find($id);

        if (!$type) {
            return redirect()
                ->route('admin.organisations.types-relation.index')
                ->with('error', 'Type de relation non trouvé');
        }

        $validator = $this->validateTypeRelation($request, $id);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::transaction(function () use ($type, $validator) {
                $type->update($validator->validated());
            });

            return redirect()
                ->route('admin.organisations.types-relation.index')
                ->with('success', 'Type de relation mis à jour avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour du type de relation: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprime un type de relation
     * DELETE /admin/organisations/types-relation/{id}
     */
    public function destroyTypeRelation($id)
    {
        $type = TypesRelation::find($id);

        if (!$type) {
            return redirect()
                ->route('admin.organisations.types-relation.index')
                ->with('error', 'Type de relation non trouvé');
        }

        try {            
            DB::transaction(function () use ($type) {
                $type->delete();
            });

            return redirect()
                ->route('admin.organisations.types-relation.index')
                ->with('success', 'Type de relation supprimé avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.organisations.types-relation.index')
                ->with('error', 'Erreur lors de la suppression du type de relation: ' . $e->getMessage());
        }
    }

    /* ==========
       Validation
    ============= */

    private function validateOrganisation(Request $request, $isUpdate = false)
    {
        $rules = [
            'nom' => ($isUpdate ? 'sometimes|' : 'required|') . 'string|max:200',
            'description' => 'nullable|string',
            'logo' => ($isUpdate ? 'sometimes|' : 'nullable|') . 'file|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'lien' => 'nullable|url|max:255',
        ];

        $messages = [
            'nom.required' => 'Le nom de l\'organisation est obligatoire',
            'nom.max' => 'Le nom ne doit pas dépasser 200 caractères',
            'logo.file' => 'Le logo doit être un fichier',
            'logo.mimes' => 'Le logo doit être une image (jpg, jpeg, png, gif, webp)',
            'logo.max' => 'Le logo ne doit pas dépasser 5MB',
            'lien.url' => 'Le lien doit être une URL valide',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    private function validateAssociation(Request $request, $id = null)
    {
        $rules = [
            'organisation_id' => 'required|integer|exists:organisations,id',
            'projet_id' => 'required|integer|exists:projets,id',
            'type_relation_id' => 'required|integer|exists:types_relation,id',
        ];

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($v) use ($request, $id) {
            $org = (int) $request->organisation_id;
            $proj = (int) $request->projet_id;
            $type = (int) $request->type_relation_id;

            $exists = OrganisationProjet::where('organisation_id', $org)
                ->where('projet_id', $proj)
                ->where('type_relation_id', $type);

            if ($id) {
                $exists->where('id', '!=', $id);
            }

            if ($exists->exists()) {
                $v->errors()->add(
                    'combinaison',
                    "Cette organisation est déjà associée à ce projet avec ce type de relation."
                );
            }
        });

        return $validator;
    }

    private function validateTypeRelation(Request $request, $id = null)
    {
        $rules = [
            'type' => 'required|string|max:100|unique:types_relation,type' . ($id ? ',' . $id : ''),
        ];

        $messages = [
            'type.required' => 'Le type de relation est obligatoire',
            'type.max' => 'Le type ne doit pas dépasser 100 caractères',
            'type.unique' => 'Ce type de relation existe déjà',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }
}