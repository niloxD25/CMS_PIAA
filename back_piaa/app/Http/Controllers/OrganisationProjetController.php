<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrganisationProjet;
use App\Models\Organisations;
use App\Models\TypesRelation;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;


class OrganisationProjetController extends Controller
{
    /* =======================
       Gestion des Associations
       organisation_projet
    ======================= */

    public function indexAssociations(Request $request)
    {
        $query = OrganisationProjet::with([
            'organisations',
            'projets',
            'types_relation'
        ]);

        // Filtres optionnels
        if ($request->has('projet_id')) {
            $query->where('projet_id', $request->projet_id);
        }

        if ($request->has('type_relation_id')) {
            $query->where('type_relation_id', $request->type_relation_id);
        }

        if ($request->has('organisation_id')) {
            $query->where('organisation_id', $request->organisation_id);
        }

        $associations = $query->get();

        return ApiResponse::success($associations, "Liste des associations récupérée avec succès");
    }

    public function storeAssociation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organisation_id' => 'required|integer|exists:organisations,id',
            'projet_id'       => 'required|integer|exists:projets,id',
            'type_relation_id'=> 'required|integer|exists:types_relation,id',
        ]);

        $validator->after(function ($v) use ($request) {
            $org  = (int) $request->organisation_id;
            $proj = (int) $request->projet_id;
            $type = (int) $request->type_relation_id;

            $exists = OrganisationProjet::where('organisation_id', $org)
                ->where('projet_id', $proj)
                ->where('type_relation_id', $type)
                ->exists();

            if ($exists) {
                $v->errors()->add(
                    'combinaison',
                    "Cette organisation est déjà associée à ce projet avec ce type de relation."
                );
            }
        });

        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        try {
            $association = DB::transaction(function () use ($validator) {
                return OrganisationProjet::create($validator->validated());
            });

            return ApiResponse::success($association, "Association créée avec succès", 201);
        } catch (\Exception $e) {
            return ApiResponse::error("Erreur lors de la création de l'association", $e->getMessage(), 500);
        }
    }

    public function showAssociation($id)
    {
        $association = OrganisationProjet::with(['organisations','projets','types_relation'])->find($id);
        if (!$association) return ApiResponse::error("Association non trouvée", null, 404);
        return ApiResponse::success($association, "Détails de l'association récupérés avec succès");
    }

    public function updateAssociation(Request $request, $id)
    {
        $association = OrganisationProjet::find($id);
        if (!$association) return ApiResponse::error("Association non trouvée", null, 404);

        $validator = Validator::make($request->all(), [
            'organisation_id' => 'sometimes|integer|exists:organisations,id',
            'projet_id'       => 'sometimes|integer|exists:projets,id',
            'type_relation_id'=> 'sometimes|integer|exists:types_relation,id',
        ]);

        $validator->after(function ($v) use ($request, $association) {
            $org  = (int) $request->input('organisation_id', $association->organisation_id);
            $proj = (int) $request->input('projet_id',       $association->projet_id);
            $type = (int) $request->input('type_relation_id',$association->type_relation_id);

            $exists = OrganisationProjet::where('organisation_id', $org)
                ->where('projet_id', $proj)
                ->where('type_relation_id', $type)
                ->where('id', '!=', $association->id)
                ->exists();

            if ($exists) {
                $v->errors()->add(
                    'combinaison',
                    "Cette organisation est déjà associée à ce projet avec ce type de relation."
                );
            }
        });

        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", $validator->errors(), 422);
        }

        try {
            DB::transaction(function () use ($association, $validator) {
                $association->update($validator->validated());
            });

            return ApiResponse::success($association->fresh(), "Association mise à jour avec succès");
        } catch (\Exception $e) {
            return ApiResponse::error("Erreur lors de la mise à jour de l'association", $e->getMessage(), 500);
        }
    }

    public function destroyAssociation($id)
    {
        $association = OrganisationProjet::find($id);
        if (!$association) return ApiResponse::error("Association non trouvée", null, 404);

        try {
            $association->delete();
            return ApiResponse::success(null, "Association supprimée avec succès");
        } catch (\Exception $e) {
            return ApiResponse::error("Erreur lors de la suppression de l'association", $e->getMessage(), 500);
        }
    }


    /* =======================
       Gestion des Organisations
    ======================= */

    public function indexOrganisations()
    {
        return ApiResponse::success(Organisations::all(), "Liste des organisations récupérée avec succès");
    }

    public function storeOrganisation(Request $request)
    {
        $validator = $this->validateData($request, 'organisation');
        if ($validator->fails()) return ApiResponse::error("Erreur de validation", $validator->errors(), 422);

        $data = $validator->validated();
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('organisations', 'public');
            $data['logo'] = '/storage/' . $path;
        }

        try {
            $organisation = DB::transaction(function () use ($data) {
                return Organisations::create($data);
            });

            return ApiResponse::success($organisation, "Organisation créée avec succès", 201);
        } catch (\Exception $e) {
            // Supprimer le fichier uploadé en cas d'erreur
            if (!empty($data['logo']) && \Storage::disk('public')->exists(str_replace('/storage/', '', $data['logo']))) {
                \Storage::disk('public')->delete(str_replace('/storage/', '', $data['logo']));
            }
            return ApiResponse::error("Erreur lors de la création de l'organisation", $e->getMessage(), 500);
        }
    }

    public function showOrganisation($id)
    {
        $organisation = Organisations::find($id);
        if (!$organisation) return ApiResponse::error("Organisation non trouvée", null, 404);
        return ApiResponse::success($organisation, "Détails de l'organisation récupérés avec succès");
    }

    public function updateOrganisation(Request $request, $id)
    {
        $organisation = Organisations::find($id);
        if (!$organisation) return ApiResponse::error("Organisation non trouvée", null, 404);

        $validator = $this->validateData($request, 'organisation', true);
        if ($validator->fails()) return ApiResponse::error("Erreur de validation", $validator->errors(), 422);

        $data = $validator->validated();
        $oldLogo = $organisation->logo;
        $newLogoPath = null;

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('organisations', 'public');
            $newLogoPath = '/storage/' . $path;
            $data['logo'] = $newLogoPath;
        }

        try {
            DB::transaction(function () use ($organisation, $data) {
                $organisation->update($data);
            });

            if ($newLogoPath && $oldLogo && \Storage::disk('public')->exists(str_replace('/storage/', '', $oldLogo))) {
                \Storage::disk('public')->delete(str_replace('/storage/', '', $oldLogo));
            }

            return ApiResponse::success($organisation->fresh(), "Organisation mise à jour avec succès");
        } catch (\Exception $e) {
            if ($newLogoPath && \Storage::disk('public')->exists(str_replace('/storage/', '', $newLogoPath))) {
                \Storage::disk('public')->delete(str_replace('/storage/', '', $newLogoPath));
            }
            return ApiResponse::error("Erreur lors de la mise à jour de l'organisation", $e->getMessage(), 500);
        }
    }

    public function destroyOrganisation($id)
    {
        $organisation = Organisations::find($id);
        if (!$organisation) return ApiResponse::error("Organisation non trouvée", null, 404);

        try {
            $organisation->delete();
            return ApiResponse::success(null, "Organisation supprimée avec succès");
        } catch (\Exception $e) {
            return ApiResponse::error("Erreur lors de la suppression de l'organisation", $e->getMessage(), 500);
        }
    }


    /* =======================
       Gestion des Types de relation
    ======================= */

    public function indexTypesRelation()
    {
        return ApiResponse::success(TypesRelation::all(), "Liste des types de relation récupérée avec succès");
    }

    public function storeTypeRelation(Request $request)
    {
        $validator = $this->validateData($request, 'type_relation');
        if ($validator->fails()) return ApiResponse::error("Erreur de validation", $validator->errors(), 422);

        try {
            $type = TypesRelation::create($validator->validated());
            return ApiResponse::success($type, "Type de relation créé avec succès", 201);
        } catch (\Exception $e) {
            return ApiResponse::error("Erreur lors de la création du type de relation", $e->getMessage(), 500);
        }
    }

    public function showTypeRelation($id)
    {
        $type = TypesRelation::find($id);
        if (!$type) return ApiResponse::error("Type de relation non trouvé", null, 404);
        return ApiResponse::success($type, "Détails du type de relation récupérés avec succès");
    }

    public function updateTypeRelation(Request $request, $id)
    {
        $type = TypesRelation::find($id);
        if (!$type) return ApiResponse::error("Type de relation non trouvé", null, 404);

        $validator = $this->validateData($request, 'type_relation', true);
        if ($validator->fails()) return ApiResponse::error("Erreur de validation", $validator->errors(), 422);

        try {
            $type->update($validator->validated());
            return ApiResponse::success($type, "Type de relation mis à jour avec succès");
        } catch (\Exception $e) {
            return ApiResponse::error("Erreur lors de la mise à jour du type de relation", $e->getMessage(), 500);
        }
    }

    public function destroyTypeRelation($id)
    {
        $type = TypesRelation::find($id);
        if (!$type) return ApiResponse::error("Type de relation non trouvé", null, 404);

        try {
            $type->delete();
            return ApiResponse::success(null, "Type de relation supprimé avec succès");
        } catch (\Exception $e) {
            return ApiResponse::error("Erreur lors de la suppression du type de relation", $e->getMessage(), 500);
        }
    }


    /* ==========
       Validation
    ============= */
    private function validateData(Request $request, $type, $isUpdate = false)
    {
        $rules = [];

        switch ($type) {
            case 'association':
                $associationId = $isUpdate ? $request->route('id') : null;

                $rules = [
                    'organisation_id' => [$isUpdate ? 'sometimes' : 'required','integer','exists:organisations,id'],
                    'projet_id'       => [$isUpdate ? 'sometimes' : 'required','integer','exists:projets,id'],
                    'type_relation_id'=> [
                        $isUpdate ? 'sometimes' : 'required',
                        'integer',
                        'exists:types_relation,id',
                        Rule::unique('organisation_projet')
                            ->where(function ($query) use ($request) {
                                if ($request->organisation_id && $request->projet_id && $request->type_relation_id) {
                                    return $query
                                        ->where('organisation_id', $request->organisation_id)
                                        ->where('projet_id', $request->projet_id)
                                        ->where('type_relation_id', $request->type_relation_id);
                                }
                            })
                            ->ignore($associationId)
                    ],
                ];
                break;

            case 'organisation':
                $rules = [
                    'nom' => ($isUpdate ? 'sometimes|' : 'required|') . 'string|max:200',
                    'description' => 'nullable|string',
                    'logo' => $isUpdate ? 'sometimes|file|mimes:jpg,jpeg,png' : 'nullable|file|mimes:jpg,jpeg,png,gif',
                    'lien' => 'nullable|string|max:255',
                ];
                break;

            case 'type_relation':
                $rules = [
                    'type' => ($isUpdate ? 'sometimes|' : 'required|') . 'string|max:100',
                ];
                break;
        }

        return Validator::make($request->all(), $rules, [
            'type_relation_id.unique' => "Cette organisation est déjà associée à ce projet avec ce type de relation."
        ]);
    }


}
