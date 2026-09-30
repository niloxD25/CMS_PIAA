<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Messages;
use App\Models\TypesMessage;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiResponse;

class MessageController extends Controller
{
    /* ===================
       Types de message
    =================== */

    public function typesIndex()
    {
        $types = TypesMessage::all();
        return ApiResponse::success($types, "Liste des types de messages récupérée");
    }

    public function typesStore(Request $request)
    {
        $validator = $this->validateRequest($request, 'types_store');
        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        $type = TypesMessage::create($validator->validated());
        return ApiResponse::success($type, "Type de message créé avec succès", 201);
    }

    public function typesUpdate(Request $request, $id)
    {
        $type = TypesMessage::find($id);
        if (!$type) {
            return ApiResponse::error("Type de message non trouvé", null, 404);
        }

        $validator = $this->validateRequest($request, 'types_update', $id);
        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        $type->update($validator->validated());
        return ApiResponse::success($type, "Type de message mis à jour avec succès");
    }

    public function typesDestroy($id)
    {
        $type = TypesMessage::find($id);
        if (!$type) {
            return ApiResponse::error("Type de message non trouvé", null, 404);
        }

        $type->delete();
        return ApiResponse::success(null, "Type de message supprimé avec succès");
    }

    /* =============
       Messages
    ============= */

    public function index(Request $request)
    {
        $query = Messages::with('types_message');

        if ($request->has('type_message_id')) {
            $query->where('type_message_id', $request->query('type_message_id'));
        }

        $messages = $query->get();
        return ApiResponse::success($messages, "Liste des messages récupérée");
    }

    public function store(Request $request)
    {
        $validator = $this->validateRequest($request, 'messages_store');
        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        $message = Messages::create($validator->validated());
        $message->load('types_message');
        return ApiResponse::success($message, "Message créé avec succès", 201);
    }

    public function show($id)
    {
        $message = Messages::with('types_message')->find($id);
        if (!$message) {
            return ApiResponse::error("Message non trouvé", null, 404);
        }

        return ApiResponse::success($message, "Message récupéré avec succès");
    }

    public function update(Request $request, $id)
    {
        $message = Messages::find($id);
        if (!$message) {
            return ApiResponse::error("Message non trouvé", null, 404);
        }

        $validator = $this->validateRequest($request, 'messages_update', $id);
        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        $message->update($validator->validated());
        $message->load('types_message');
        return ApiResponse::success($message, "Message mis à jour avec succès");
    }

    public function destroy($id)
    {
        $message = Messages::find($id);
        if (!$message) {
            return ApiResponse::error("Message non trouvé", null, 404);
        }

        $message->delete();
        return ApiResponse::success(null, "Message supprimé avec succès");
    }

    /* =============
       Validation
    ============= */
    private function validateRequest(Request $request, string $type, $id = null)
    {
        $rules = [];

        switch ($type) {
            case 'types_store':
                $rules = ['type' => 'required|string|max:255|unique:types_message,type'];
                break;

            case 'types_update':
                $rules = ['type' => 'required|string|max:255|unique:types_message,type,' . $id];
                break;

            case 'messages_store':
                $rules = [
                    'nom' => 'required|string|max:255',
                    'mail' => 'nullable|email|max:255',
                    'telephone' => 'nullable|string|max:20',
                    'message' => 'required|string',
                    'type_message_id' => 'nullable|exists:types_message,id',
                ];
                
                // Ajouter la valeur par défaut si non fournie
                if (!$request->has('type_message_id') || $request->type_message_id === null) {
                    $request->merge(['type_message_id' => 3]);
                }
                break;

            case 'messages_update':
                $rules = [
                    'nom' => 'sometimes|required|string|max:255',
                    'mail' => 'nullable|email|max:255',
                    'telephone' => 'nullable|string|max:20',
                    'message' => 'sometimes|required|string',
                    'type_message_id' => 'nullable|exists:types_message,id',
                ];
                
                // Pour l'update, on ne modifie que si le champ est présent mais vide
                if ($request->has('type_message_id') && $request->type_message_id === null) {
                    $request->merge(['type_message_id' => 3]);
                }
                break;
        }

        return Validator::make($request->all(), $rules);
    }
}
