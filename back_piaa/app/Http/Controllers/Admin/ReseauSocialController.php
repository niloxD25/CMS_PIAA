<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReseauxSociaux;
use App\Models\Projets;
use App\Models\ProjetsReseauxSociaux;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReseauSocialController extends Controller
{
    /**
     * Affiche la liste des réseaux sociaux disponibles
     */
    public function index()
    {
        $reseauxSociaux = ReseauxSociaux::orderBy('nom')->get();

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
        
        foreach ($reseauxSociaux as $reseau) {
            $reseau->font_awesome_icon = $iconMapping[$reseau->icone] ?? 'fas fa-question';
        }

        return view('admin.reseaux-sociaux.index', compact('reseauxSociaux'));
    }

    /**
     * Affiche le formulaire de création d'un réseau social
     */
    public function create()
    {
        return view('admin.reseaux-sociaux.create');
    }

    /**
     * Enregistre un nouveau réseau social
     */
    public function store(Request $request)
    {
        $validator = $this->validateReseauSocial($request);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            ReseauxSociaux::create($validator->validated());

            return redirect()
                ->route('admin.reseaux-sociaux.index')
                ->with('success', 'Réseau social créé avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la création du réseau social: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Affiche le formulaire d'édition d'un réseau social
     */
    public function edit($id)
    {
        $reseauSocial = ReseauxSociaux::find($id);

        if (!$reseauSocial) {
            return redirect()
                ->route('admin.reseaux-sociaux.index')
                ->with('error', 'Réseau social non trouvé');
        }

        return view('admin.reseaux-sociaux.edit', compact('reseauSocial'));
    }

    /**
     * Met à jour un réseau social existant
     */
    public function update(Request $request, $id)
    {
        $reseauSocial = ReseauxSociaux::find($id);

        if (!$reseauSocial) {
            return redirect()
                ->route('admin.reseaux-sociaux.index')
                ->with('error', 'Réseau social non trouvé');
        }

        $validator = $this->validateReseauSocial($request, true);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $reseauSocial->update($validator->validated());

            return redirect()
                ->route('admin.reseaux-sociaux.index')
                ->with('success', 'Réseau social mis à jour avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour du réseau social: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprime un réseau social
     */
    public function destroy($id)
    {
        $reseauSocial = ReseauxSociaux::find($id);

        if (!$reseauSocial) {
            return redirect()
                ->route('admin.reseaux-sociaux.index')
                ->with('error', 'Réseau social non trouvé');
        }

        // Vérifier si le réseau social est utilisé par des projets
        if ($reseauSocial->projets()->count() > 0) {
            return redirect()
                ->route('admin.reseaux-sociaux.index')
                ->with('error', 'Impossible de supprimer ce réseau social car il est associé à des projets');
        }

        try {
            $reseauSocial->delete();

            return redirect()
                ->route('admin.reseaux-sociaux.index')
                ->with('success', 'Réseau social supprimé avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.reseaux-sociaux.index')
                ->with('error', 'Erreur lors de la suppression du réseau social: ' . $e->getMessage());
        }
    }

    /**
     * Ajouter un réseau social à un projet (CREATE)
     */
    public function addToProjet(Request $request, $projetId)
    {
        $projet = Projets::find($projetId);
        
        if (!$projet) {
            return redirect()->back()->with('error', 'Projet non trouvé');
        }

        $validator = Validator::make($request->all(), [
            'reseau_social_id' => 'required|exists:reseaux_sociaux,id',
            'url' => 'required|url|max:500'
        ], [
            'reseau_social_id.required' => 'Le réseau social est obligatoire',
            'reseau_social_id.exists' => 'Le réseau social sélectionné n\'existe pas',
            'url.required' => 'L\'URL est obligatoire',
            'url.url' => 'L\'URL doit être une adresse valide',
            'url.max' => 'L\'URL ne doit pas dépasser 500 caractères'
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Vérifier si le réseau social est déjà associé au projet
            $existing = ProjetsReseauxSociaux::where('projet_id', $projetId)
                ->where('reseau_social_id', $request->reseau_social_id)
                ->exists();
            
            if ($existing) {
                return redirect()->back()->with('error', 'Ce réseau social est déjà associé à ce projet');
            }

            // CREATE - Créer une nouvelle relation
            ProjetsReseauxSociaux::create([
                'projet_id' => $projetId,
                'reseau_social_id' => $request->reseau_social_id,
                'url' => $request->url
            ]);
            
            return redirect()->back()->with('success', 'Réseau social ajouté avec succès');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de l\'ajout du réseau social: ' . $e->getMessage());
        }
    }

    /**
     * Modifier un réseau social d'un projet (UPDATE)
     */
    public function updateInProjet(Request $request, $projetId, $reseauSocialId)
    {
        $projet = Projets::find($projetId);
        
        if (!$projet) {
            return redirect()->back()->with('error', 'Projet non trouvé');
        }

        $validator = Validator::make($request->all(), [
            'url' => 'required|url|max:500'
        ], [
            'url.required' => 'L\'URL est obligatoire',
            'url.url' => 'L\'URL doit être une adresse valide',
            'url.max' => 'L\'URL ne doit pas dépasser 500 caractères'
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // UPDATE - Mettre à jour la relation existante
            $projetReseauSocial = ProjetsReseauxSociaux::where('projet_id', $projetId)
                ->where('reseau_social_id', $reseauSocialId)
                ->first();

            if (!$projetReseauSocial) {
                return redirect()->back()->with('error', 'Relation projet-réseau social non trouvée');
            }

            $projetReseauSocial->update([
                'url' => $request->url
            ]);
            
            return redirect()->back()->with('success', 'Réseau social modifié avec succès');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la modification du réseau social: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un réseau social d'un projet (DELETE)
     */
    public function removeFromProjet($projetId, $reseauSocialId)
    {
        $projet = Projets::find($projetId);
        
        if (!$projet) {
            return redirect()->back()->with('error', 'Projet non trouvé');
        }

        try {
            // DELETE - Supprimer la relation
            $projetReseauSocial = ProjetsReseauxSociaux::where('projet_id', $projetId)
                ->where('reseau_social_id', $reseauSocialId)
                ->first();

            if (!$projetReseauSocial) {
                return redirect()->back()->with('error', 'Relation projet-réseau social non trouvée');
            }

            $projetReseauSocial->delete();
            
            return redirect()->back()->with('success', 'Réseau social supprimé avec succès');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression du réseau social: ' . $e->getMessage());
        }
    }

    /**
     * Validation des données du réseau social
     */
    private function validateReseauSocial(Request $request, $isUpdate = false)
    {
        $rules = [
            'nom' => ($isUpdate ? 'sometimes|' : '') . 'required|string|max:50|unique:reseaux_sociaux,nom',
            'icone' => 'required|string|max:255'
        ];

        $messages = [
            'nom.required' => 'Le nom du réseau social est obligatoire',
            'nom.max' => 'Le nom ne doit pas dépasser 50 caractères',
            'nom.unique' => 'Ce nom de réseau social existe déjà',
            'icone.required' => 'L\'icône est obligatoire',
            'icone.max' => 'L\'icône ne doit pas dépasser 255 caractères'
        ];

        // Pour l'update, ignorer l'unicité pour l'id actuel
        if ($isUpdate) {
            $rules['nom'] .= ',' . $request->route('id');
        }

        return Validator::make($request->all(), $rules, $messages);
    }
}