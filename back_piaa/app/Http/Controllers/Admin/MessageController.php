<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Messages;
use App\Models\TypesMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * Affiche la liste des messages
     * GET /admin/messages
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $sortBy = $request->get('sort_by', 'date_envoi');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Définition des colonnes autorisées pour le tri
        $allowedSortColumns = ['nom', 'mail', 'type_message_id', 'date_envoi', 'is_read'];
        $allowedSortOrders = ['asc', 'desc'];
        
        // Validation des paramètres de tri
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'date_envoi';
        $sortOrder = in_array($sortOrder, $allowedSortOrders) ? $sortOrder : 'desc';
        
        // Construction de la requête de base
        $query = Messages::with('types_message');
        
        // Récupération de tous les filtres
        $filters = $request->only(['type_message_id', 'is_read', 'search']);
        
        // Application des filtres
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                if ($key === 'is_read') {
                    $query->where('is_read', $value == '1');
                } elseif ($key === 'search') {
                    $searchTerm = $value;
                    $query->where(function($q) use ($searchTerm) {
                        $q->where('nom', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('mail', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('telephone', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('message', 'LIKE', "%{$searchTerm}%");
                    });
                } else {
                    $query->where($key, $value);
                }
            }
        }
        
        // Gestion du tri
        if ($sortBy === 'type_message_id') {
            // Tri par relation types_message
            $query->leftJoin('types_message', 'messages.type_message_id', '=', 'types_message.id')
                ->select('messages.*')
                ->orderBy('types_message.type', $sortOrder);
        } else {
            // Tri normal
            $query->orderBy($sortBy, $sortOrder);
        }
        
        // Pagination
        $messages = $query->paginate($perPage);
        
        // Compter les messages non lus/lus pour l'affichage
        $unreadCount = Messages::where('is_read', false)->count();
        $readCount = Messages::where('is_read', true)->count();
        
        // Récupérer les types de messages pour les filtres
        $typesMessage = TypesMessage::all();
        
        // Ajouter tous les paramètres à la pagination
        $appends = [
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
        ];
        
        // Ajouter seulement les filtres non vides
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $appends[$key] = $value;
            }
        }
        
        $messages->appends($appends);
        
        return view('admin.messages.index', compact(
            'messages', 
            'typesMessage', 
            'sortBy', 
            'sortOrder', 
            'unreadCount', 
            'readCount'
        ));
    }

    /**
     * Affiche les détails d'un message
     * GET /admin/messages/{id}
     */
    public function show($id)
    {
        $message = Messages::with('types_message')->find($id);

        if (!$message) {
            return redirect()
                ->route('admin.messages.index')
                ->with('error', 'Message non trouvé');
        }

        if (!$message->is_read) {
            $message->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }

        return view('admin.messages.show', compact('message'));
    }

    /**
     * Met à jour le type d'un message
     * PUT /admin/messages/{id}/update-type
     */
    public function updateType(Request $request, $id)
    {
        $message = Messages::find($id);

        if (!$message) {
            return redirect()
                ->back()
                ->with('error', 'Message non trouvé');
        }

        $validator = Validator::make($request->all(), [
            'type_message_id' => 'nullable|exists:types_message,id'
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->with('error', 'Type de message invalide');
        }

        try {
            $message->update([
                'type_message_id' => $request->type_message_id ?: 3
            ]);

            return redirect()
                ->back()
                ->with('success', 'Type de message mis à jour avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour du type: ' . $e->getMessage());
        }
    }

    /**
     * Supprime un message
     * DELETE /admin/messages/{id}
     */
    public function destroy($id)
    {
        $message = Messages::find($id);

        if (!$message) {
            return redirect()
                ->route('admin.messages.index')
                ->with('error', 'Message non trouvé');
        }

        try {            
            DB::transaction(function () use ($message) {
                $message->delete();
            });

            return redirect()
                ->route('admin.messages.index')
                ->with('success', 'Message supprimé avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.messages.index')
                ->with('error', 'Erreur lors de la suppression du message: ' . $e->getMessage());
        }
    }

    // Pour marquer comme lu
    public function markAsRead($id)
    {
        $message = Messages::find($id);

        if (!$message) {
            return response()->json(['error' => 'Message non trouvé'], 404);
        }

        try {
            $message->update([
                'is_read' => true,
                'read_at' => now()
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /* ===================
       Types de message
    =================== */

    /**
     * Affiche la liste des types de messages
     * GET /admin/types-messages
     */
    public function typesIndex()
    {
        $types = TypesMessage::all();
        return view('admin.messages.types-index', compact('types'));
    }

    /**
     * Affiche le formulaire de création d'un type
     * GET /admin/types-messages/create
     */
    public function typesCreate()
    {
        return view('admin.messages.types-create');
    }

    /**
     * Enregistre un nouveau type de message
     * POST /admin/types-messages
     */
    public function typesStore(Request $request)
    {
        $validator = $this->validateType($request);
        
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::transaction(function () use ($validator) {
                TypesMessage::create($validator->validated());
            });

            return redirect()
                ->route('admin.types-messages.index')
                ->with('success', 'Type de message créé avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la création du type de message: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Affiche le formulaire d'édition d'un type
     * GET /admin/types-messages/{id}/edit
     */
    public function typesEdit($id)
    {
        $type = TypesMessage::find($id);

        if (!$type) {
            return redirect()
                ->route('admin.types-messages.index')
                ->with('error', 'Type de message non trouvé');
        }

        return view('admin.messages.types-edit', compact('type'));
    }

    /**
     * Met à jour un type de message
     * PUT /admin/types-messages/{id}
     */
    public function typesUpdate(Request $request, $id)
    {
        $type = TypesMessage::find($id);

        if (!$type) {
            return redirect()
                ->route('admin.types-messages.index')
                ->with('error', 'Type de message non trouvé');
        }

        $validator = $this->validateType($request, $id);
        
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
                ->route('admin.types-messages.index')
                ->with('success', 'Type de message mis à jour avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour du type de message: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprime un type de message
     * DELETE /admin/types-messages/{id}
     */
    public function typesDestroy($id)
    {
        $type = TypesMessage::find($id);

        if (!$type) {
            return redirect()
                ->route('admin.types-messages.index')
                ->with('error', 'Type de message non trouvé');
        }

        try {            
            DB::transaction(function () use ($type) {
                $type->delete();
            });

            return redirect()
                ->route('admin.types-messages.index')
                ->with('success', 'Type de message supprimé avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.types-messages.index')
                ->with('error', 'Erreur lors de la suppression du type de message: ' . $e->getMessage());
        }
    }

    /**
     * Validation des types de messages
     */
    private function validateType(Request $request, $id = null)
    {
        $rules = [
            'type' => 'required|string|max:255|unique:types_message,type' . ($id ? ',' . $id : ''),
        ];

        $messages = [
            'type.required' => 'Le type de message est obligatoire',
            'type.max' => 'Le type ne doit pas dépasser 255 caractères',
            'type.unique' => 'Ce type de message existe déjà',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }
}