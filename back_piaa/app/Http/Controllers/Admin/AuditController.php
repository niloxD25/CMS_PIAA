<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;
use Illuminate\Support\Facades\Auth;

class AuditController extends Controller
{
    /**
     * Affiche la liste des audits
     * GET /admin/audits
     */
    public function index(Request $request)
    {
        $user = Auth::user()->load('typeCompte');

        if ($user->typeCompte->id == 1) 
        {
            $perPage = $request->get('per_page', 20);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            $allowedSortColumns = ['created_at', 'user'];
            $allowedSortOrders = ['asc', 'desc'];
            
            $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'created_at';
            $sortOrder = in_array($sortOrder, $allowedSortOrders) ? $sortOrder : 'desc';

            $query = Audit::with(['user']);

            $filters = $request->only(['event', 'auditable_type', 'user_id']);
            foreach ($filters as $key => $value) {
                if ($value !== null && $value !== '') {
                    if ($key === 'auditable_type') {
                        $query->where('auditable_type', 'like', "%{$value}%");
                    } else {
                        $query->where("audits.{$key}", $value);
                    }
                }
            }

            // Gestion du tri par utilisateur (relation)
            if ($sortBy === 'user') {
                $query->leftJoin('users', 'audits.user_id', '=', 'users.id')
                    ->select('audits.*')
                    ->orderBy('users.name', $sortOrder);
            } else {
                $query->orderBy("audits.{$sortBy}", $sortOrder);
            }

            $audits = $query->paginate($perPage);

            // Options pour les filtres
            $events = ['created', 'updated', 'deleted', 'restored'];
            $auditableTypes = Audit::distinct()->pluck('auditable_type')->map(function ($type) {
                return class_basename($type);
            })->unique();

            $audits->appends([
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ] + $filters);

            return view('admin.audits.index', compact(
                'audits', 
                'events', 
                'auditableTypes',
                'sortBy', 
                'sortOrder'
            ));
        }
        else {
            return redirect()->route('admin.content.dashboard');
        }
    }
    
    /**
     * Affiche les détails d'un audit
     * GET /admin/audits/{id}
     */
    public function show($id)
    {
        $audit = Audit::with([
            'user',
            'auditable' => function ($query) {
                $query->withTrashed();
            }
        ])->find($id);

        if (!$audit) {
            return redirect()->route('admin.audits.index')
                ->with('error', 'Audit non trouvé');
        }

        return view('admin.audits.show', compact('audit'));
    }

    /**
     * Restorer un objet supprimé
     * POST /admin/audits/{audit}/restore
     */
    public function restore($id)
    {
        $audit = Audit::with(['auditable' => function ($q) {
            $q->withTrashed();
        }])->find($id);

        if (!$audit || !$audit->auditable) {
            return redirect()->route('admin.audits.index')->with('error', "Objet non trouvé.");
        }

        $model = $audit->auditable;

        if (method_exists($model, 'restore') && $model->trashed()) {
            $model->restore();

            return redirect()->route('admin.audits.show', $audit->id)
                ->with('success', class_basename($model) . " restauré avec succès.");
        }

        return redirect()->route('admin.audits.show', $audit->id)
            ->with('info', "L'objet n'est pas supprimé ou ne peut pas être restauré.");
    }

    /**
     * Suprimer définitivement un objet supprimé
     * POST /admin/audits/{audit}/force-delete
     */
    public function forceDelete($id)
    {
        // Vérifier si l'utilisateur est administrateur
        if (auth()->user()->type_compte_id != 1) {
            return redirect()
                ->route('admin.audits.show', $id)
                ->with('error', 'Vous n\'avez pas les permissions nécessaires pour supprimer définitivement des objets.');
        }
        
        $audit = Audit::with(['auditable' => function ($q) {
            $q->withTrashed();
        }])->find($id);

        if (!$audit || !$audit->auditable) {
            return redirect()->route('admin.audits.index')->with('error', "Objet non trouvé.");
        }

        $model = $audit->auditable;

        if (method_exists($model, 'forceDelete')) {
            try {
                $model->forceDelete();

                return redirect()->route('admin.audits.show', $audit->id)
                    ->with('success', class_basename($model) . " supprimé définitivement.");
            } catch (\Throwable $e) {
                return redirect()->route('admin.audits.show', $audit->id)
                    ->with('error', "Erreur lors de la suppression définitive : " . $e->getMessage());
            }
        }

        return redirect()->route('admin.audits.show', $audit->id)
            ->with('info', "Ce modèle ne supporte pas la suppression définitive.");
    }

}