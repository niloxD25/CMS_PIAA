<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjetPersonnes;
use App\Models\Projets;
use App\Models\Personnes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProjetPersonneController extends Controller
{
    /**
     * Affiche la liste des participants d'un projet
     * GET /admin/projets/{projetId}/participants
     */
    public function index(Request $request, $projetId)
    {
        $projet = Projets::find($projetId);
        
        if (!$projet) {
            return redirect()
                ->route('admin.projets.index')
                ->with('error', 'Projets non trouvé');
        }

        $perPage = $request->get('per_page', 10);
        
        $participants = ProjetPersonnes::with(['personnes'])
            ->where('projet_id', $projetId)
            ->orderBy('date_debut_participation', 'desc')
            ->paginate($perPage);
        
        return view('admin.projets.participants.index', compact('participants', 'projet'));
    }

    /**
     * Affiche le formulaire d'ajout d'un participant
     * GET /admin/projets/{projetId}/participants/create
     */
    public function create($projetId)
    {
        $projet = Projets::find($projetId);
        
        if (!$projet) {
            return redirect()
                ->route('admin.projets.index')
                ->with('error', 'Projets non trouvé');
        }

        $personnes = Personnes::orderBy('nom')->orderBy('prenom')->get();
        
        return view('admin.projets.participants.create', compact('projet', 'personnes'));
    }

    /**
     * Enregistre un nouveau participant
     * POST /admin/projets/{projetId}/participants
     */
    public function store(Request $request, $projetId)
    {
        $projet = Projets::find($projetId);
        
        if (!$projet) {
            return redirect()
                ->route('admin.projets.index')
                ->with('error', 'Projets non trouvé');
        }

        $validator = $this->validateData($request, $projetId, null, false);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::transaction(function () use ($validator, $projetId) {
                $data = $validator->validated();
                $data['projet_id'] = $projetId;
                ProjetPersonnes::create($data);
            });

            return redirect()
                ->back()
                ->with('success', 'Participant ajouté avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de l\'ajout du participant: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Affiche les détails d'une participation
     * GET /admin/projets/{projetId}/participants/{id}
     */
    public function show($projetId, $id)
    {
        $projet = Projets::find($projetId);
        
        if (!$projet) {
            return redirect()
                ->route('admin.projets.index')
                ->with('error', 'Projets non trouvé');
        }

        $participation = ProjetPersonnes::with(['personne', 'projet'])
            ->where('projet_id', $projetId)
            ->where('id', $id)
            ->first();

        if (!$participation) {
            return redirect()
                ->back()
                ->with('error', 'Participation non trouvée');
        }

        return view('admin.projets.participants.show', compact('participation', 'projet'));
    }

    /**
     * Affiche le formulaire d'édition d'une participation
     * GET /admin/projets/{projetId}/participants/{id}/edit
     */
    public function edit($projetId, $id)
    {
        $projet = Projets::find($projetId);
        
        if (!$projet) {
            return redirect()
                ->route('admin.projets.index')
                ->with('error', 'Projets non trouvé');
        }

        $participation = ProjetPersonnes::with(['personnes'])
            ->where('projet_id', $projetId)
            ->where('id', $id)
            ->first();

        if (!$participation) {
            return redirect()
                ->back()
                ->with('error', 'Participation non trouvée');
        }

        $personnes = Personnes::orderBy('nom')->orderBy('prenom')->get();
        
        return view('admin.projets.participants.edit', compact('participation', 'projet', 'personnes'));
    }

    /**
     * Met à jour une participation existante
     * PUT /admin/projets/{projetId}/participants/{id}
     */
    public function update(Request $request, $projetId, $id)
    {
        $projet = Projets::find($projetId);
        
        if (!$projet) {
            return redirect()
                ->route('admin.projets.index')
                ->with('error', 'Projets non trouvé');
        }

        $participation = ProjetPersonnes::where('projet_id', $projetId)
            ->where('id', $id)
            ->first();

        if (!$participation) {
            return redirect()
                ->back()
                ->with('error', 'Participation non trouvée');
        }

        $validator = $this->validateData($request, $projetId, $participation->id, true);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::transaction(function () use ($participation, $validator) {
                $participation->update($validator->validated());
            });

            return redirect()
                ->back()
                ->with('success', 'Participation mise à jour avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour de la participation: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprime une participation
     * DELETE /admin/projets/{projetId}/participants/{id}
     */
    public function destroy($projetId, $id)
    {
        $projet = Projets::find($projetId);
        
        if (!$projet) {
            return redirect()
                ->route('admin.projets.index')
                ->with('error', 'Projets non trouvé');
        }

        $participation = ProjetPersonnes::where('projet_id', $projetId)
            ->where('id', $id)
            ->first();

        if (!$participation) {
            return redirect()
                ->back()
                ->with('error', 'Participation non trouvée');
        }

        try {            
            DB::transaction(function () use ($participation) {
                $participation->delete();
            });

            return redirect()
                ->back()
                ->with('success', 'Participant retiré du projet avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors du retrait du participant: ' . $e->getMessage());
        }
    }

    /**
     * Validation des données de participation
     */
    private function validateData(Request $request, $projetId, $excludeId = null, $isUpdate = false)
    {
        $uniqueRule = 'unique:projet_personnes,personne_id,NULL,id,projet_id,' . $projetId;
        
        if ($excludeId) {
            $uniqueRule = 'unique:projet_personnes,personne_id,' . $excludeId . ',id,projet_id,' . $projetId;
        }

        $rules = [
            'personne_id' => ($isUpdate ? 'sometimes|' : '') . 'required|integer|exists:personnes,id|' . $uniqueRule,
            'date_debut_participation' => ($isUpdate ? 'sometimes|' : '') . 'required|date',
            'hierarchie' => 'nullable|integer|min:1',
            'date_fin_participation' => 'nullable|date|after:date_debut_participation',
        ];

        $messages = [
            'personne_id.required' => 'La personne est obligatoire',
            'personne_id.exists' => 'La personne sélectionnée n\'existe pas',
            'personne_id.unique' => 'Cette personne participe déjà à ce projet',
            'hierarchie.integer' => 'L\'ordre d\'affichage doit être un nombre entier',
            'hierarchie.min' => 'L\'ordre d\'affichage doit être d\'au moins 1',
            'date_debut_participation.required' => 'La date de début de participation est obligatoire',
            'date_debut_participation.date' => 'La date de début doit être une date valide',
            'date_fin_participation.date' => 'La date de fin doit être une date valide',
            'date_fin_participation.after' => 'La date de fin doit être après la date de début',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }
}