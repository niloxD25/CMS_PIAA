<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Annonces;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnnonceController extends Controller
{
    /**
     * Affiche la liste des annonces
     * GET /admin/annonces
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $sortBy = $request->get('sort_by', 'date_affichage');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Validation des paramètres de tri
        $allowedSortColumns = ['titre', 'date_affichage', 'nbr_jours_affichage'];
        $allowedSortOrders = ['asc', 'desc'];
        
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'date_affichage';
        $sortOrder = in_array($sortOrder, $allowedSortOrders) ? $sortOrder : 'desc';
        
        $annonces = Annonces::orderBy($sortBy, $sortOrder)->paginate($perPage);
        
        // Garder tous les paramètres dans les liens de pagination
        $annonces->appends([
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder
        ]);
        
        return view('admin.annonces.index', compact('annonces', 'sortBy', 'sortOrder'));
    }

    /**
     * Affiche le formulaire de création
     * GET /admin/annonces/create
     */
    public function create()
    {
        return view('admin.annonces.create');
    }

    /**
     * Enregistre une nouvelle annonce
     * POST /admin/annonces
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

        try {
            DB::transaction(function () use ($validator) {
                Annonces::create($validator->validated());
            });

            return redirect()
                ->route('admin.annonces.index')
                ->with('success', 'Annonce créée avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la création de l\'annonce: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Affiche les détails d'une annonce
     * GET /admin/annonces/{id}
     */
    public function show($id)
    {
        $annonce = Annonces::find($id);

        if (!$annonce) {
            return redirect()
                ->route('admin.annonces.index')
                ->with('error', 'Annonce non trouvée');
        }

        return view('admin.annonces.show', compact('annonce'));
    }

    /**
     * Affiche le formulaire d'édition
     * GET /admin/annonces/{id}/edit
     */
    public function edit($id)
    {
        $annonce = Annonces::find($id);

        if (!$annonce) {
            return redirect()
                ->route('admin.annonces.index')
                ->with('error', 'Annonce non trouvée');
        }

        return view('admin.annonces.edit', compact('annonce'));
    }

    /**
     * Met à jour une annonce existante
     * PUT /admin/annonces/{id}
     */
    public function update(Request $request, $id)
    {
        $annonce = Annonces::find($id);

        if (!$annonce) {
            return redirect()
                ->route('admin.annonces.index')
                ->with('error', 'Annonce non trouvée');
        }

        $validator = $this->validateData($request, true);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::transaction(function () use ($annonce, $validator) {
                $annonce->update($validator->validated());
            });

            return redirect()
                ->route('admin.annonces.index')
                ->with('success', 'Annonce mise à jour avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour de l\'annonce: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprime une annonce
     * DELETE /admin/annonces/{id}
     */
    public function destroy($id)
    {
        $annonce = Annonces::find($id);

        if (!$annonce) {
            return redirect()
                ->route('admin.annonces.index')
                ->with('error', 'Annonce non trouvée');
        }

        try {            
            DB::transaction(function () use ($annonce) {
                $annonce->delete();
            });

            return redirect()
                ->route('admin.annonces.index')
                ->with('success', 'Annonce supprimée avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.annonces.index')
                ->with('error', 'Erreur lors de la suppression de l\'annonce: ' . $e->getMessage());
        }
    }

    /**
     * Validation des données de l'annonce
     */
    private function validateData(Request $request, $isUpdate = false)
    {
        $rules = [
            'titre' => ($isUpdate ? 'sometimes|' : 'required|') . 'string|max:255',
            'contenu' => 'nullable|string',
            'date_affichage' => 'nullable|date',
            'nbr_jours_affichage' => 'integer|min:1',
        ];

        $messages = [
            'titre.required' => 'Le titre de l\'annonce est obligatoire',
            'titre.max' => 'Le titre ne doit pas dépasser 255 caractères',
            'date_affichage.date' => 'La date d\'affichage doit être une date valide',
            'nbr_jours_affichage.integer' => 'Le nombre de jours doit être un nombre entier',
            'nbr_jours_affichage.min' => 'Le nombre de jours doit être d\'au moins 1',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }
}