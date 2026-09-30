<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Projets;
use App\Models\ProjetEnCours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProjetController extends Controller
{
    /**
     * Affiche la liste des projets
     * GET /admin/projets
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $sortBy = $request->get('sort_by', 'nom');
        $sortOrder = $request->get('sort_order', 'asc');
        
        // Validation des paramètres de tri
        $allowedSortColumns = ['nom', 'debut', 'duree_annee', 'email', 'telephone'];
        $allowedSortOrders = ['asc', 'desc'];
        
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'nom';
        $sortOrder = in_array($sortOrder, $allowedSortOrders) ? $sortOrder : 'asc';
        
        $query = Projets::query();
        
        // --- Logique des Filtres ---
        $filters = $request->only(['nom', 'archived']);
        
        // Recherche par nom ou acronyme
        if ($request->has('nom') && trim($request->nom) !== '') {
            $search = trim($request->nom);
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(nom) LIKE ?', ['%' . strtolower($search) . '%'])
                ->orWhereRaw('LOWER(acronymes) LIKE ?', ['%' . strtolower($search) . '%']);
            });
        }

        // Filtre par statut d'archivage
        if ($request->filled('archived')) {
            $query->where('archived', $request->archived);
        }
        
        // Exécution du tri et pagination
        $projets = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);
        
        // Garder tous les paramètres (tri + filtres) dans les liens de pagination
        $projets->appends(array_merge([
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
        ], $filters));
        
        return view('admin.projets.index', compact('projets', 'sortBy', 'sortOrder'));
    }

    /**
     * Affiche le formulaire de création
     * GET /admin/projets/create
     */
    public function create()
    {
        return view('admin.projets.create');
    }

    /**
     * Enregistre un nouveau projet
     * POST /admin/projets
     */
    public function store(Request $request)
    {
        $validator = $this->validateProjet($request);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Gestion du logo
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('projets', 'public');
            $data['logo'] = '/storage/' . $path;
        }

        try {
            DB::transaction(function () use ($data) {
                Projets::create($data);
            });

            return redirect()
                ->route('admin.projets.index')
                ->with('success', 'Projet créé avec succès');

        } catch (\Exception $e) {
            // Nettoyage du fichier en cas d'erreur
            if (isset($data['logo']) && Storage::disk('public')->exists(str_replace('/storage/', '', $data['logo']))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $data['logo']));
            }
            
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la création du projet: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Affiche les détails d'un projet
     * GET /admin/projets/{id}
     */
    public function show($id)
    {
        $projet = Projets::with(['reseaux_sociaux', 'projet_apropos_sections', 'projet_personnes' => function($query) {
            $query->with('personnes')
                ->orderBy('hierarchie');
        }])->find($id);

        if (!$projet) {
            return redirect()
                ->route('admin.projets.index')
                ->with('error', 'Projet non trouvé');
        }

        // Mapping des icônes
        $iconMapping = [
            'faFacebookF' => 'fab fa-facebook-f',
            'faXTwitter' => 'fab fa-x-twitter',
            'faYoutube' => 'fab fa-youtube',
            'faLinkedinIn' => 'fab fa-linkedin-in',
            'faInstagram' => 'fab fa-instagram',
            'faTiktok' => 'fab fa-tiktok',
            'faWhatsapp' => 'fab fa-whatsapp',
            'faTelegram' => 'fab fa-telegram',
            'faGithub' => 'fab fa-github',
            'faDiscord' => 'fab fa-discord',
            'faSlack' => 'fab fa-slack',
            'faReddit' => 'fab fa-reddit',
            'faPinterest' => 'fab fa-pinterest',
            'faSnapchat' => 'fab fa-snapchat',
            'faSkype' => 'fab fa-skype',
            'faQuestion' => 'fas fa-question'
        ];
        
        foreach ($projet->reseaux_sociaux as $reseau) {
            $reseau->font_awesome_icon = $iconMapping[$reseau->icone] ?? 'fas fa-question';
        }

        $reseauxSociaux = \App\Models\ReseauxSociaux::all();
        
        // Récupérer les IDs des personnes déjà associées
        $personnesIds = $projet->projet_personnes->pluck('personne_id')->toArray();
        
        // Récupérer les personnes qui ne sont pas encore associées à ce projet
        $personnes = \App\Models\Personnes::whereNotIn('id', $personnesIds)->get();

        return view('admin.projets.show', compact('projet', 'reseauxSociaux', 'personnes'));
    }

    /**
     * Affiche le formulaire d'édition
     * GET /admin/projets/{id}/edit
     */
    public function edit($id)
    {
        $projet = Projets::find($id);

        if (!$projet) {
            return redirect()
                ->route('admin.projets.index')
                ->with('error', 'Projet non trouvé');
        }

        return view('admin.projets.edit', compact('projet'));
    }

    /**
     * Met à jour un projet existant
     * PUT /admin/projets/{id}
     */
    public function update(Request $request, $id)
    {
        $projet = Projets::find($id);

        if (!$projet) {
            return redirect()
                ->route('admin.projets.index')
                ->with('error', 'Projet non trouvé');
        }

        $validator = $this->validateProjet($request, true);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        $oldLogo = $projet->logo;
        $newLogoPath = null;

        // Gestion du nouveau logo
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('projets', 'public');
            $newLogoPath = '/storage/' . $path;
            $data['logo'] = $newLogoPath;
        }

        try {
            DB::transaction(function () use ($data, $projet) {
                $projet->update($data);
            });

            // Supprimer l'ancien logo après succès de la mise à jour
            if ($newLogoPath && $oldLogo && Storage::disk('public')->exists(str_replace('/storage/', '', $oldLogo))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldLogo));
            }

            return redirect()
                ->route('admin.projets.index')
                ->with('success', 'Projet mis à jour avec succès');

        } catch (\Exception $e) {
            // Rollback du nouveau logo en cas d'erreur
            if ($newLogoPath && Storage::disk('public')->exists(str_replace('/storage/', '', $newLogoPath))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $newLogoPath));
            }

            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour du projet: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprime un projet
     * DELETE /admin/projets/{id}
     */
    public function destroy($id)
    {
        $projet = Projets::find($id);

        if (!$projet) {
            return redirect()
                ->route('admin.projets.index')
                ->with('error', 'Projet non trouvé');
        }

        try {            
            DB::transaction(function () use ($projet) {
                $projet->delete();
            });

            return redirect()
                ->route('admin.projets.index')
                ->with('success', 'Projet supprimé avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.projets.index')
                ->with('error', 'Erreur lors de la suppression du projet: ' . $e->getMessage());
        }
    }

    /**
     * Définit le projet actuel
     * POST /admin/projets/{id}/set-actuel
     */
    public function setProjetActuel($id)
    {
        $projet = Projets::find($id);

        if (!$projet) {
            return redirect()
                ->back()
                ->with('error', 'Projet non trouvé');
        }

        if ($projet->archived) {
            return redirect()
                ->back()
                ->with('error', 'Projet archivé, ne peut pas être défini comme projet actuel');
        }

        try {
            ProjetEnCours::create([
                'projet_id' => $id,
                'date_changement' => now(),
            ]);

            return redirect()
                ->back()
                ->with('success', 'Projet défini comme actuel avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la définition du projet actuel: ' . $e->getMessage());
        }
    }

    /**
     * Archive/Désarchive un fichier
     * PATCH /admin/projets/{id}/toggle-archive
     */
    public function toggleArchive($id)
    {
        $projet = Projets::find($id);

        if (!$projet) {
            return redirect()
                ->route('admin.projets.index')
                ->with('error', 'Projet non trouvé');
        }

        // Vérifier si le projet est le projet actuel
        $projetActuel = ProjetEnCours::latest('date_changement')->first();
        
        if ($projetActuel && $projetActuel->projet_id == $projet->id) {
            return redirect()
                ->back()
                ->with('error', 'Impossible d\'archiver le projet actuel. Veuillez d\'abord définir un autre projet comme actuel.');
        }

        try {
            $projet->update(['archived' => !$projet->archived]);
            
            $action = $projet->archived ? 'archivé' : 'désarchivé';
            
            return redirect()
                ->back()
                ->with('success', "Projet {$action} avec succès");

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.projets.index')
                ->with('error', 'Erreur lors de l\'archivage du projet: ' . $e->getMessage());
        }
    }

    /**
     * Validation des données du projet
     */
    private function validateProjet(Request $request, $isUpdate = false)
    {
        $rules = [
            'nom' => ($isUpdate ? 'sometimes|' : '') . 'required|string|max:200',
            'description' => 'nullable|string',
            'devise' => 'nullable|string|max:255',
            'acronymes' => 'nullable|string|max:30',
            'adresse' => 'nullable|string|max:200',
            'email' => 'nullable|email|max:200',
            'telephone' => 'nullable|string|max:20',
            'logo' => ($isUpdate ? 'sometimes|' : 'nullable|') . 'file|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'debut' => 'nullable|date',
            'duree_annee' => 'nullable|integer|min:0',
        ];

        $messages = [
            'nom.required' => 'Le nom du projet est obligatoire',
            'nom.max' => 'Le nom ne doit pas dépasser 200 caractères',
            'devise.max' => 'La devise ne doit pas dépasser 255 caractères',
            'acronymes.max' => 'Les acronymes ne doivent pas dépasser 30 caractères',
            'adresse.max' => 'L\'adresse ne doit pas dépasser 200 caractères',
            'email.email' => 'L\'email doit être une adresse email valide',
            'email.max' => 'L\'email ne doit pas dépasser 200 caractères',
            'telephone.max' => 'Le téléphone ne doit pas dépasser 20 caractères',
            'logo.file' => 'Le logo doit être un fichier',
            'logo.mimes' => 'Le logo doit être une image (jpg, jpeg, png, gif, webp)',
            'logo.max' => 'Le logo ne doit pas dépasser 5MB',
            'debut.date' => 'La date de début doit être une date valide',
            'duree_annee.integer' => 'La durée doit être un nombre entier',
            'duree_annee.min' => 'La durée ne peut pas être négative',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }
}