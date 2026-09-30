<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fichiers;
use App\Models\Projets;
use App\Models\EntitesFichier;
use App\Models\TypesCompte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FichierController extends Controller
{
    /**
     * Affiche la liste des fichiers
     * GET /admin/fichiers
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $sortBy = $request->get('sort_by', 'date_upload');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSortColumns = ['titre', 'type_fichier', 'date_upload', 'projet', 'niveau_acces'];
        $allowedSortOrders = ['asc', 'desc'];

        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'date_upload';
        $sortOrder = in_array($sortOrder, $allowedSortOrders) ? $sortOrder : 'desc';

        $query = Fichiers::with(['entites_fichier', 'projets'])
            ->whereNotIn('entite_type_id', [
                Fichiers::ENTITE_TYPE_EVENEMENT,
                Fichiers::ENTITE_TYPE_REALISATION
            ]);

        $filters = $request->only(['entite_type_id', 'projet_id', 'type_fichier', 'archived']);
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $query->where("fichiers.$key", $value);
            }
        }

        // Gestion du tri
        if ($sortBy === 'projet') {
            $query->leftJoin('projets', 'fichiers.projet_id', '=', 'projets.id')
                ->select('fichiers.*')
                ->orderBy('projets.nom', $sortOrder);
        } else {
            $query->orderBy("fichiers.$sortBy", $sortOrder);
        }

        $fichiers = $query->paginate($perPage);

        $projets = Projets::all();

        $entitesFichier = EntitesFichier::whereNotIn('id', [
            Fichiers::ENTITE_TYPE_EVENEMENT,
            Fichiers::ENTITE_TYPE_REALISATION
        ])->get();

        $typesFichier = Fichiers::distinct()->pluck('type_fichier')->filter();

        $fichiers->appends([
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
        ] + $filters);

        return view('admin.fichiers.index', compact('fichiers', 'projets', 'entitesFichier', 'typesFichier', 'sortBy', 'sortOrder'));
    }


    /**
     * Affiche le formulaire de création
     * GET /admin/fichiers/create
     */
    public function create()
    {
        $projets = Projets::all();

        $entitesFichier = EntitesFichier::whereNotIn('id', [
            Fichiers::ENTITE_TYPE_EVENEMENT,
            Fichiers::ENTITE_TYPE_REALISATION
        ])->get();

        $typesCompte = TypesCompte::orderBy('niveau_acces')->get();
        
        return view('admin.fichiers.create', compact('projets', 'entitesFichier', 'typesCompte'));
    }

    /**
     * Enregistre un nouveau fichier
     * POST /admin/fichiers
     */
    public function store(Request $request)
    {
        $validator = $this->validateFichier($request);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        $path = null;

        try {
            // Gestion de l'upload
            if ($request->hasFile('fichier')) {
                $path = $request->file('fichier')->store('uploads/fichiers', 'public');
                $data['chemin'] = '/storage/' . $path;
                $data['type_fichier'] = $request->file('fichier')->getClientOriginalExtension();
                $data['date_upload'] = now();
            }

            DB::transaction(function () use ($data) {
                Fichiers::create($data);
            });

            return redirect()
                ->back()
                ->with('success', 'Fichier créé avec succès');

        } catch (\Exception $e) {
            // Nettoyage du fichier en cas d'erreur
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la création du fichier: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Affiche les détails d'un fichier
     * GET /admin/fichiers/{id}
     */
    public function show($id)
    {
        $fichier = Fichiers::with(['entites_fichier', 'projets'])->find($id);

        if (!$fichier) {
            return redirect()
                ->route('admin.fichiers.index')
                ->with('error', 'Fichier non trouvé');
        }

        return view('admin.fichiers.show', compact('fichier'));
    }

    /**
     * Affiche le formulaire d'édition
     * GET /admin/fichiers/{id}/edit
     */
    public function edit($id)
    {
        $fichier = Fichiers::find($id);

        $projets = Projets::all();

        $entitesFichier = EntitesFichier::whereNotIn('id', [
            Fichiers::ENTITE_TYPE_EVENEMENT,
            Fichiers::ENTITE_TYPE_REALISATION
        ])->get();
        
        $typesCompte = TypesCompte::orderBy('niveau_acces')->get();

        if (!$fichier) {
            return redirect()
                ->route('admin.fichiers.index')
                ->with('error', 'Fichier non trouvé');
        }

        return view('admin.fichiers.edit', compact('fichier', 'projets', 'entitesFichier', 'typesCompte'));
    }

    /**
     * Met à jour un fichier existant
     * PUT /admin/fichiers/{id}
     */
    public function update(Request $request, $id)
    {
        $fichier = Fichiers::find($id);

        if (!$fichier) {
            return redirect()
                ->back()
                ->with('error', 'Fichier non trouvé');
        }

        $validator = $this->validateFichier($request, true);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        $oldPath = $fichier->chemin;
        $newPath = null;

        try {
            DB::transaction(function () use ($data, $fichier, $request) {
                // Gestion du remplacement de fichier
                if ($request->hasFile('fichier')) {
                    $newPath = $request->file('fichier')->store('uploads/fichiers', 'public');
                    $data['chemin'] = '/storage/' . $newPath;
                    $data['type_fichier'] = $request->file('fichier')->getClientOriginalExtension();
                    $data['date_upload'] = now();
                }

                $fichier->update($data);
            });

            // Supprimer l'ancien fichier après succès de la mise à jour
            if ($newPath && $oldPath && Storage::disk('public')->exists(str_replace('/storage/', '', $oldPath))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldPath));
            }

            return redirect()
                ->back()
                ->with('success', 'Fichier mis à jour avec succès');

        } catch (\Exception $e) {
            // Rollback du nouveau fichier en cas d'erreur
            if ($newPath && Storage::disk('public')->exists($newPath)) {
                Storage::disk('public')->delete($newPath);
            }

            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour du fichier: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprime un fichier
     * DELETE /admin/fichiers/{id}
     */
    public function destroy($id)
    {
        $fichier = Fichiers::find($id);

        if (!$fichier) {
            return redirect()
                ->route('admin.fichiers.index')
                ->with('error', 'Fichier non trouvé');
        }

        try {            
            DB::transaction(function () use ($fichier) {
                $fichier->delete();
            });
            return redirect()
                ->back()
                ->with('success', 'Fichier supprimé avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.fichiers.index')
                ->with('error', 'Erreur lors de la suppression du fichier: ' . $e->getMessage());
        }
    }

    /**
     * Télécharge un fichier
     * GET /admin/fichiers/{id}/download
     */
    public function download($id)
    {
        $fichier = Fichiers::find($id);

        if (!$fichier) {
            return redirect()
                ->route('admin.fichiers.index')
                ->with('error', 'Fichier non trouvé');
        }

        $cheminSansStorage = str_replace('/storage/', '', $fichier->chemin);

        if (!$fichier->chemin || !Storage::disk('public')->exists($cheminSansStorage)) {
            return redirect()
                ->route('admin.fichiers.index')
                ->with('error', 'Fichier introuvable sur le serveur');
        }

        $fullPath = storage_path('app/public/' . $cheminSansStorage);

        $nomTelechargement = $fichier->titre 
            ? $fichier->titre . ($fichier->type_fichier ? '.' . $fichier->type_fichier : '')
            : basename($fichier->chemin);

        return response()->download($fullPath, $nomTelechargement);
    }

    /**
     * Archive/Désarchive un fichier
     * PATCH /admin/fichiers/{id}/toggle-archive
     */
    public function toggleArchive($id)
    {
        $fichier = Fichiers::find($id);

        if (!$fichier) {
            return redirect()
                ->route('admin.fichiers.index')
                ->with('error', 'Fichier non trouvé');
        }

        try {
            $fichier->update(['archived' => !$fichier->archived]);
            
            $action = $fichier->archived ? 'archivé' : 'désarchivé';
            
            return redirect()
                ->back()
                ->with('success', "Fichier {$action} avec succès");

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.fichiers.index')
                ->with('error', 'Erreur lors de l\'archivage du fichier: ' . $e->getMessage());
        }
    }

    /* ==========
       Validation
    ============= */

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
            'fichier' => ($isUpdate ? 'sometimes|' : 'required|') . 'file|max:50240'
        ];

        $messages = [
            'titre.max' => 'Le titre ne doit pas dépasser 200 caractères',
            'projet_id.exists' => 'Le projet sélectionné n\'existe pas',
            'entite_type_id.required' => 'Le type d\'entité est obligatoire',
            'entite_type_id.exists' => 'Le type d\'entité sélectionné n\'existe pas',
            'niveau_acces.required' => 'Le niveau d\'accès est obligatoire',
            'niveau_acces.exists' => 'Le niveau d\'accès sélectionné n\'existe pas',
            'fichier.required' => 'Le fichier est obligatoire',
            'fichier.file' => 'Le fichier doit être un fichier valide',
            'fichier.max' => 'Le fichier ne doit pas dépasser 50MB',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }


    /**
     * Retourne l'icône appropriée pour le type de fichier
     */
    protected function getFileIcon($fileType)
    {
        $icons = [
            'pdf' => 'fas fa-file-pdf',
            'doc' => 'fas fa-file-word',
            'docx' => 'fas fa-file-word',
            'xls' => 'fas fa-file-excel',
            'xlsx' => 'fas fa-file-excel',
            'jpg' => 'fas fa-file-image',
            'jpeg' => 'fas fa-file-image',
            'png' => 'fas fa-file-image',
            'gif' => 'fas fa-file-image',
            'zip' => 'fas fa-file-archive',
            'rar' => 'fas fa-file-archive',
            'txt' => 'fas fa-file-alt',
        ];

        return $icons[strtolower($fileType)] ?? 'fas fa-file';
    }

    /**
     * Retourne la classe CSS pour l'icône du fichier
     */
    protected function getFileIconClass($fileType)
    {
        $types = [
            'pdf' => 'pdf',
            'doc' => 'doc', 'docx' => 'doc',
            'xls' => 'xls', 'xlsx' => 'xls',
            'jpg' => 'img', 'jpeg' => 'img', 'png' => 'img', 'gif' => 'img',
            'zip' => 'zip', 'rar' => 'zip',
            'txt' => 'txt',
        ];

        return $types[strtolower($fileType)] ?? 'default';
    }

    /**
     * Retourne la taille formatée du fichier
     */
    protected function getFileSize($filePath)
    {
        $cleanPath = str_replace('/storage/', '', $filePath);
        
        if (!Storage::disk('public')->exists($cleanPath)) {
            return 'Inconnue';
        }

        $size = Storage::disk('public')->size($cleanPath);
        
        if ($size >= 1073741824) {
            return number_format($size / 1073741824, 2) . ' GB';
        } elseif ($size >= 1048576) {
            return number_format($size / 1048576, 2) . ' MB';
        } elseif ($size >= 1024) {
            return number_format($size / 1024, 2) . ' KB';
        } else {
            return $size . ' bytes';
        }
    }
}