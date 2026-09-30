<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evenements;
use App\Models\Projets;
use App\Models\ChangementEtatEvenement;
use App\Models\EtatsEvenement;
use App\Models\TypesCompte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Fichiers;

class EvenementController extends Controller
{
    /**
     * Affiche la liste des événements
     * GET /admin/evenements
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $sortBy = $request->get('sort_by', 'date_debut');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSortColumns = ['titre', 'date_debut', 'date_fin', 'projet'];
        $allowedSortOrders = ['asc', 'desc'];
        
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'date_debut';
        $sortOrder = in_array($sortOrder, $allowedSortOrders) ? $sortOrder : 'desc';
        
        $query = Evenements::with(['projets', 'etatCourant.etats_evenement']);
        
        $filters = $request->only(['projet_id', 'archived', 'est_actualite', 'etat_id']);
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                if ($key === 'etat_id') {
                    $query->whereHas('etatCourant', function($q) use ($value) {
                        $q->where('etat_evenement_id', $value);
                    });
                } else {
                    $query->where("evenements.$key", $value);
                }
            }
        }

        // Gestion du tri par projet (relation)
        if ($sortBy === 'projet') {
            $query->leftJoin('projets', 'evenements.projet_id', '=', 'projets.id')
                ->select('evenements.*')
                ->orderBy('projets.nom', $sortOrder);
        } else {
            $query->orderBy("evenements.$sortBy", $sortOrder);
        }
        
        $evenements = $query->paginate($perPage);
        $projets = Projets::all();
        $etatsEvenement = EtatsEvenement::all();
        
        $evenements->appends([
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
        ] + $filters);
        
        return view('admin.evenements.index', compact('evenements', 'projets', 'etatsEvenement', 'sortBy', 'sortOrder'));
    }

    /**
     * Affiche le formulaire de création
     * GET /admin/evenements/create
     */
    public function create()
    {
        $projets = Projets::all();
        $etatsEvenement = EtatsEvenement::all();
        $typesCompte = TypesCompte::orderBy('niveau_acces')->get();
        
        return view('admin.evenements.create', compact('projets', 'etatsEvenement', 'typesCompte'));
    }

    /**
     * Enregistre un nouvel événement
     * POST /admin/evenements
     */
    public function store(Request $request)
    {
        $validator = $this->validateEvenement($request);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Gestion de l'image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('evenements', 'public');
            $data['image'] = '/storage/' . $path;
        }

        try {
            DB::transaction(function () use ($data) {
                $evenement = Evenements::create($data);

                // Créer le premier changement d'état
                ChangementEtatEvenement::create([
                    'evenement_id' => $evenement->id,
                    'etat_evenement_id' => $data['etat_evenement_id'] ?? 1,
                    'date' => now(),
                ]);
            });

            return redirect()
                ->route('admin.evenements.index')
                ->with('success', 'Événement créé avec succès');

        } catch (\Exception $e) {
            // Nettoyage du fichier en cas d'erreur
            if (isset($data['image']) && Storage::disk('public')->exists(str_replace('/storage/', '', $data['image']))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $data['image']));
            }
            
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la création de l\'événement: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Affiche les détails d'un événement
     * GET /admin/evenements/{id}
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

        $entiteTypeEvenementId = Fichiers::ENTITE_TYPE_EVENEMENT;

        $typesCompte = TypesCompte::orderBy('niveau_acces')->get();

        if (!$evenement) {
            return redirect()
                ->route('admin.evenements.index')
                ->with('error', 'Événement non trouvé');
        }

        return view('admin.evenements.show', compact('evenement', 'entiteTypeEvenementId', 'typesCompte'));
    }

    /**
     * Affiche le formulaire d'édition
     * GET /admin/evenements/{id}/edit
     */
    public function edit($id)
    {
        $evenement = Evenements::with(['etatCourant.etats_evenement'])->find($id);
        $projets = Projets::all();
        $etatsEvenement = EtatsEvenement::all();
        $typesCompte = TypesCompte::orderBy('niveau_acces')->get();

        if (!$evenement) {
            return redirect()
                ->route('admin.evenements.index')
                ->with('error', 'Événement non trouvé');
        }

        return view('admin.evenements.edit', compact('evenement', 'projets', 'etatsEvenement', 'typesCompte'));
    }

    /**
     * Met à jour un événement existant
     * PUT /admin/evenements/{id}
     */
    public function update(Request $request, $id)
    {
        $evenement = Evenements::find($id);

        if (!$evenement) {
            return redirect()
                ->route('admin.evenements.index')
                ->with('error', 'Événement non trouvé');
        }

        $validator = $this->validateEvenement($request, true);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        $oldImage = $evenement->image;
        $newImagePath = null;

        // Gestion de la nouvelle image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('evenements', 'public');
            $newImagePath = '/storage/' . $path;
            $data['image'] = $newImagePath;
        }

        try {
            DB::transaction(function () use ($data, $evenement) {
                $evenement->update($data);

                // Créer un nouveau changement d'état si l'état a changé
                if (isset($data['etat_evenement_id']) && 
                    $evenement->etatCourant->etat_evenement_id !== $data['etat_evenement_id']) {
                    ChangementEtatEvenement::create([
                        'evenement_id' => $evenement->id,
                        'etat_evenement_id' => $data['etat_evenement_id'],
                        'date' => now(),
                    ]);
                }
            });

            // Supprimer l'ancienne image après succès de la mise à jour
            if ($newImagePath && $oldImage && Storage::disk('public')->exists(str_replace('/storage/', '', $oldImage))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldImage));
            }

            return redirect()
                ->route('admin.evenements.index')
                ->with('success', 'Événement mis à jour avec succès');

        } catch (\Exception $e) {
            // Rollback de la nouvelle image en cas d'erreur
            if ($newImagePath && Storage::disk('public')->exists(str_replace('/storage/', '', $newImagePath))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $newImagePath));
            }

            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour de l\'événement: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprime un événement
     * DELETE /admin/evenements/{id}
     */
    public function destroy($id)
    {
        $evenement = Evenements::find($id);

        if (!$evenement) {
            return redirect()
                ->route('admin.evenements.index')
                ->with('error', 'Événement non trouvé');
        }

        try {            
            DB::transaction(function () use ($evenement) {
                $evenement->delete();
            });

            return redirect()
                ->route('admin.evenements.index')
                ->with('success', 'Événement supprimé avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.evenements.index')
                ->with('error', 'Erreur lors de la suppression de l\'événement: ' . $e->getMessage());
        }
    }

    /**
     * Basculer l'état d'actualité d'un événement
     * PATCH /admin/evenements/{id}/toggle-actualite
     */
    public function toggleActualite($id)
    {
        $evenement = Evenements::find($id);

        if (!$evenement) {
            return redirect()
                ->route('admin.evenements.index')
                ->with('error', 'Événement non trouvé');
        }

        try {
            $evenement->update(['est_actualite' => !$evenement->est_actualite]);
            
            $action = $evenement->est_actualite ? 'marqué comme actualité' : 'retiré des actualités';
            
            return redirect()
                ->route('admin.evenements.index')
                ->with('success', "Événement {$action} avec succès");

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.evenements.index')
                ->with('error', 'Erreur lors du changement d\'état d\'actualité: ' . $e->getMessage());
        }
    }

    /**
     * Archive/Désarchive un événement
     * PATCH /admin/evenements/{id}/toggle-archive
     */
    public function toggleArchive($id)
    {
        $evenement = Evenements::find($id);

        if (!$evenement) {
            return redirect()
                ->route('admin.evenements.index')
                ->with('error', 'Événement non trouvé');
        }

        try {
            $evenement->update(['archived' => !$evenement->archived]);
            
            $action = $evenement->archived ? 'archivé' : 'désarchivé';
            
            return redirect()
                ->route('admin.evenements.index')
                ->with('success', "Événement {$action} avec succès");

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.evenements.index')
                ->with('error', 'Erreur lors de l\'archivage de l\'événement: ' . $e->getMessage());
        }
    }

    /**
     * Affiche une section
     * GET /admin/evenements/{evenement}/sections/{section}
     */
    public function showSection($evenementId, $sectionId)
    {
        $evenement = Evenements::find($evenementId);
        if (!$evenement) {
            return response()->json(['error' => 'Événement non trouvé'], 404);
        }

        $section = $evenement->sections()->find($sectionId);
        if (!$section) {
            return response()->json(['error' => 'Section non trouvée'], 404);
        }

        return response()->json($section);
    }

    /**
     * Crée une nouvelle section
     * POST /admin/evenements/{evenement}/sections
     */
    public function storeSection(Request $request, $evenementId)
    {
        $evenement = Evenements::find($evenementId);
        if (!$evenement) {
            return redirect()
                ->back()
                ->with('error', 'Événement non trouvé');
        }

        $validator = $this->validateSection($request);
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->with('error', 'Erreur de validation: ' . $validator->errors()->first());
        }

        $data = $validator->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('sections', 'public');
            $data['image'] = '/storage/' . $path;
        }

        try {
            $evenement->sections()->create($data);
            return redirect()
                ->back()
                ->with('success', 'Section créée avec succès');

        } catch (\Exception $e) {
            // rollback image si DB échoue
            if (isset($data['image']) && Storage::disk('public')->exists(str_replace('/storage/', '', $data['image']))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $data['image']));
            }
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la création de la section: ' . $e->getMessage());
        }
    }

    /**
     * Met à jour une section
     * PUT /admin/evenements/{evenement}/sections/{section}
     */
    public function updateSection(Request $request, $evenementId, $sectionId)
    {
        $evenement = Evenements::find($evenementId);
        if (!$evenement) {
            return redirect()
                ->back()
                ->with('error', 'Événement non trouvé');
        }

        $section = $evenement->sections()->find($sectionId);
        if (!$section) {
            return redirect()
                ->back()
                ->with('error', 'Section non trouvée');
        }

        $validator = $this->validateSection($request, true);
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->with('error', 'Erreur de validation: ' . $validator->errors()->first());
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
            $section->update($data);

            // Supprimer l'ancienne image après succès
            if ($newImagePath && $oldImage && Storage::disk('public')->exists(str_replace('/storage/', '', $oldImage))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldImage));
            }

            return redirect()
                ->back()
                ->with('success', 'Section mise à jour avec succès');

        } catch (\Exception $e) {
            // rollback fichier si erreur DB
            if ($newImagePath && Storage::disk('public')->exists(str_replace('/storage/', '', $newImagePath))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $newImagePath));
            }

            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour de la section: ' . $e->getMessage());
        }
    }

    /**
     * Supprime une section
     * DELETE /admin/evenements/{evenement}/sections/{section}
     */
    public function destroySection($evenementId, $sectionId)
    {
        $evenement = Evenements::find($evenementId);
        if (!$evenement) {
            return redirect()
                ->back()
                ->with('error', 'Événement non trouvé');
        }

        $section = $evenement->sections()->find($sectionId);
        if (!$section) {
            return redirect()
                ->back()
                ->with('error', 'Section non trouvée');
        }

        try {
            $section->delete();

            return redirect()
                ->back()
                ->with('success', 'Section supprimée avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la suppression de la section: ' . $e->getMessage());
        }
    }

    /**
     * Affiche les détails d'un fichier
     * GET /admin/evenements/fichiers/{id}
     */
    public function showFichier($id)
    {
        $fichier = Fichiers::with(['entites_fichier', 'projets'])->find($id);

        if (!$fichier) {
            return response()->json(['error' => 'Fichier non trouvé'], 404);
        }

        $fichier->file_icon = Fichiers::getFileIconStatic($fichier->type_fichier);
        $fichier->file_icon_class = Fichiers::getFileIconClassStatic($fichier->type_fichier);
        $fichier->file_size = Fichiers::getFileSizeStatic($fichier->chemin);

        return response()->json($fichier);
    }

    /**
     * Crée des fichiers
     * POST /admin/evenements/fichiers
     */
    public function storeFichier(Request $request)
    {
        $validator = $this->validateFichier($request);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        $uploadedPaths = [];
        $successCount = 0;
        $errorCount = 0;

        try {
            DB::transaction(function () use ($request, $data, &$uploadedPaths, &$successCount, &$errorCount) {
                if ($request->hasFile('fichiers')) {
                    foreach ($request->file('fichiers') as $file) {
                        $fileData = $data;
                        $path = null;
                        
                        try {
                            $path = $file->store('uploads/fichiers', 'public');
                            $fileData['chemin'] = '/storage/' . $path;
                            $fileData['type_fichier'] = $file->getClientOriginalExtension();
                            $fileData['date_upload'] = now();
                            
                            Fichiers::create($fileData);
                            
                            $uploadedPaths[] = $path;
                            $successCount++;
                            
                        } catch (\Exception $e) {
                            $errorCount++;
                            if ($path && Storage::disk('public')->exists($path)) {
                                Storage::disk('public')->delete($path);
                            }
                            continue;
                        }
                    }
                }
            });

            $message = '';
            if ($successCount > 0) {
                $message = $successCount . ' fichier(s) créé(s) avec succès';
            }
            if ($errorCount > 0) {
                $message .= ($message ? ' - ' : '') . $errorCount . ' fichier(s) en erreur';
            }

            return redirect()
                ->back()
                ->with($successCount > 0 ? 'success' : 'warning', $message);

        } catch (\Exception $e) {
            foreach ($uploadedPaths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
            
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la création des fichiers: ' . $e->getMessage())
                ->withInput();
        }
    }

    /* ==========
       Validation
    ============= */
    private function validateEvenement(Request $request, $isUpdate = false)
    {
        $rules = [
            'titre' => ($isUpdate ? 'sometimes|' : 'required|') . 'string|max:200',
            'description' => 'nullable|string',
            'date_debut' => ($isUpdate ? 'sometimes|' : 'required|') . 'date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'image' => ($isUpdate ? 'sometimes|' : 'nullable|') . 'file|mimes:jpg,jpeg,png|max:5120',
            'projet_id' => ($isUpdate ? 'sometimes|' : 'required|') . 'integer|exists:projets,id',
            'niveau_acces' => ($isUpdate ? 'sometimes|' : 'required|') . 'integer|exists:types_compte,niveau_acces',
            'archived' => 'nullable|boolean',
            'est_actualite' => 'nullable|boolean',
            'etat_evenement_id' => 'nullable|integer|exists:etats_evenement,id'
        ];

        $messages = [
            'titre.required' => 'Le titre est obligatoire',
            'titre.max' => 'Le titre ne doit pas dépasser 200 caractères',
            'date_debut.required' => 'La date de début est obligatoire',
            'date_debut.date' => 'La date de début doit être une date valide',
            'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début',
            'image.file' => 'L\'image doit être un fichier',
            'image.mimes' => 'L\'image doit être au format JPG, JPEG ou PNG',
            'image.max' => 'L\'image ne doit pas dépasser 5MB',
            'projet_id.required' => 'Le projet est obligatoire',
            'projet_id.exists' => 'Le projet sélectionné n\'existe pas',
            'niveau_acces.required' => 'Le niveau d\'accès est obligatoire',
            'niveau_acces.exists' => 'Le niveau d\'accès sélectionné n\'existe pas',
        ];

        return Validator::make($request->all(), $rules, $messages);
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

    /**
     * Validation fichiers
     */
    private function validateFichier(Request $request, $isUpdate = false)
    {
        $rules = [
            'titre' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'projet_id' => 'nullable|integer|exists:projets,id',
            'entite_type_id' => ($isUpdate ? 'sometimes|' : 'required|') . 'integer|exists:entites_fichier,id',
            'entite_id' => 'nullable|integer',
            'niveau_acces' => ($isUpdate ? 'sometimes|' : 'required|') . 'integer|exists:types_compte,niveau_acces',
            'archived' => 'nullable|boolean',
        ];

        if ($isUpdate) {
            $rules['fichier'] = 'sometimes|file|max:50240';
        } else {
            $rules['fichiers'] = 'required|array|min:1';
            $rules['fichiers.*'] = 'file|max:50240';
        }

        $messages = [
            'titre.max' => 'Le titre ne doit pas dépasser 200 caractères',
            'projet_id.exists' => 'Le projet sélectionné n\'existe pas',
            'entite_type_id.required' => 'Le type d\'entité est obligatoire',
            'entite_type_id.exists' => 'Le type d\'entité sélectionné n\'existe pas',
            'niveau_acces.required' => 'Le niveau d\'accès est obligatoire',
            'niveau_acces.exists' => 'Le niveau d\'accès sélectionné n\'existe pas',
            'fichiers.required' => 'Au moins un fichier est obligatoire',
            'fichiers.array' => 'Les fichiers doivent être un tableau',
            'fichiers.min' => 'Au moins un fichier est obligatoire',
            'fichiers.*.file' => 'Chaque fichier doit être un fichier valide',
            'fichiers.*.max' => 'Chaque fichier ne doit pas dépasser 50MB',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }
}