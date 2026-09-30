<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TypesCompte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Affiche la liste des utilisateurs
     * GET /admin/users
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');

        $allowedSortColumns = ['name', 'email', 'type_compte_id', 'created_at'];
        $allowedSortOrders = ['asc', 'desc'];

        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'name';
        $sortOrder = in_array($sortOrder, $allowedSortOrders) ? $sortOrder : 'asc';

        $query = User::with(['typeCompte']);

        if (auth()->user()->type_compte_id == 1) {
            $typesCompte = TypesCompte::get();
        }
        else {
            $query->whereNotIn('type_compte_id', [1, 2]);
            $typesCompte = TypesCompte::whereNotIn('id', [1, 2])->get();
        }

        // Appliquer seulement les filtres remplis
        $filters = $request->only(['type_compte_id']);
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $query->where("users.$key", $value);
            }
        }

        // Recherche par nom ou email
        if ($request->has('search') && trim($request->search) !== '') {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
                ->orWhereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%']);
            });
        }

        // Tri
        $query->orderBy("users.$sortBy", $sortOrder);

        $users = $query->paginate($perPage);

        $users->appends([
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
            'search' => $request->search,
        ] + $filters);

        return view('admin.users.index', compact('users', 'typesCompte', 'sortBy', 'sortOrder'));
    }

    /**
     * Affiche le formulaire de création
     * GET /admin/users/create
     */
    public function create()
    {
        if (auth()->user()->type_compte_id == 1) {
            $typesCompte = TypesCompte::get();
        }
        else {
            $typesCompte = TypesCompte::whereNotIn('id', [1, 2])->get();
        }
        
        return view('admin.users.create', compact('typesCompte'));
    }

    /**
     * Enregistre un nouvel utilisateur
     * POST /admin/users
     */
    public function store(Request $request)
    {
        $validator = $this->validateUser($request);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Hash du mot de passe
        $data['password'] = Hash::make($data['password']);
        // $data['email_verified_at'] = now();

        try {
            DB::transaction(function () use ($data) {
                User::create($data);
            });

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Utilisateur créé avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la création de l\'utilisateur: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Affiche les détails d'un utilisateur
     * GET /admin/users/{id}
     */
    public function show($id)
    {
        $user = User::with(['typeCompte'])->find($id);

        if (!$user) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Utilisateur non trouvé');
        }

        return view('admin.users.show', compact('user'));
    }

    /**
     * Affiche le formulaire d'édition
     * GET /admin/users/{id}/edit
     */
    public function edit($id)
    {
        $user = User::find($id);
        if (auth()->user()->type_compte_id == 1) {
            $typesCompte = TypesCompte::get();
        }
        else {
            $typesCompte = TypesCompte::whereNotIn('id', [1, 2])->get();
        }

        if (!$user) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Utilisateur non trouvé');
        }

        return view('admin.users.edit', compact('user', 'typesCompte'));
    }

    /**
     * Met à jour un utilisateur existant
     * PUT /admin/users/{id}
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Utilisateur non trouvé');
        }

        $validator = $this->validateUser($request, true, $user->id);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Hash du nouveau mot de passe si fourni
        if (isset($data['password']) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        try {
            DB::transaction(function () use ($data, $user) {
                $user->update($data);
            });

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Utilisateur mis à jour avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour de l\'utilisateur: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprime un utilisateur
     * DELETE /admin/users/{id}
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Utilisateur non trouvé');
        }

        // Empêcher la suppression de son propre compte
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte');
        }

        try {            
            DB::transaction(function () use ($user) {
                $user->delete();
            });

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Utilisateur supprimé avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Erreur lors de la suppression de l\'utilisateur: ' . $e->getMessage());
        }
    }

    /* ==========
       Validation
    ============= */

    private function validateUser(Request $request, $isUpdate = false, $userId = null)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'type_compte_id' => 'required|integer|exists:types_compte,id',
        ];

        // Règles pour l'email
        if ($isUpdate) {
            $rules['email'] = 'required|email|max:255|unique:users,email,' . $userId;
        } else {
            $rules['email'] = 'required|email|max:255|unique:users';
        }

        // Règles pour le mot de passe
        if ($isUpdate) {
            $rules['password'] = 'nullable|string|min:8|confirmed';
        } else {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $messages = [
            'name.required' => 'Le nom est obligatoire',
            'name.max' => 'Le nom ne doit pas dépasser 255 caractères',
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'L\'email doit être une adresse valide',
            'email.unique' => 'Cet email est déjà utilisé',
            'email.max' => 'L\'email ne doit pas dépasser 255 caractères',
            'password.required' => 'Le mot de passe est obligatoire',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',
            'password.confirmed' => 'Les mots de passe ne correspondent pas',
            'type_compte_id.required' => 'Le type de compte est obligatoire',
            'type_compte_id.exists' => 'Le type de compte sélectionné n\'existe pas',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }
}