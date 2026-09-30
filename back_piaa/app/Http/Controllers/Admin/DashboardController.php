<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Models\Audit;
use App\Models\Fichiers;
use App\Models\Projets;
use App\Models\Evenements;
use App\Models\Messages;
use App\Models\User;
use App\Models\Annonces;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Redirige vers le dashboard approprié selon le rôle
     */
    public function redirectToDashboard()
    {
        $user = Auth::user()->load('typeCompte');
        
        if ($user->typeCompte->id == 1) {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('admin.content.dashboard');
        }
    }

    /**
     * Dashboard Administrateur (type_compte 1)
     */
    public function adminDashboard()
    {
        $user = Auth::user()->load('typeCompte');
        
        // Vérifier que l'utilisateur est bien admin
        if ($user->typeCompte->id != 1) {
            return redirect()->route('admin.content.dashboard');
        }

        $role = 'Administrateur';
        
        $audits = Audit::where('user_id', $user->id)
            ->latest()
            ->get();

        $allAudits = Audit::latest()->take(50)->get();

        $chartData = $this->prepareChartData();

        return view('admin.dashboards.admin', compact('user', 'allAudits', 'audits', 'role', 'chartData'));
    }

    private function prepareChartData()
    {
        $firstAudit = Audit::oldest()->first();
        $startDate = $firstAudit ? $firstAudit->created_at->startOfDay() : now()->subDays(30);
        $endDate = now();

        $labels = [];
        $created = [];
        $updated = [];
        $deleted = [];
        $restored = [];

        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $labels[] = $current->format('Y-m-d');

            $startOfDay = $current->copy()->startOfDay();
            $endOfDay = $current->copy()->endOfDay();

            $created[] = Audit::where('event', 'created')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->count();

            $updated[] = Audit::where('event', 'updated')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->count();

            $deleted[] = Audit::where('event', 'deleted')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->count();

            $restored[] = Audit::where('event', 'restored')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->count();

            $current->addDay();
        }

        $mergedData = [];
        foreach ($labels as $i => $label) {
            $mergedData[] = [
                'date' => $label,
                'created' => $created[$i],
                'updated' => $updated[$i],
                'deleted' => $deleted[$i],
                'restored' => $restored[$i],
            ];
        }

        return $mergedData;
    }

    /**
     * Dashboard Content Manager (type_compte 2)
     */
    public function contentDashboard()
    {
        $user = auth()->user();

        if ($user->typeCompte->id != 2) {
            return redirect()->route('admin.dashboard');
        }

        $role = 'Content Manager';
        $now = Carbon::now();

        // Exemples de statistiques
        $stats = [
            'mes_projets' => Projets::count(),
            'evenements_actifs' => Evenements::whereHas('etatCourant', function ($query) {
                $query->where('etat_evenement_id', 3);
            })->count(),
            'fichiers_uploades' => Fichiers::whereNotIn('entite_type_id', [Fichiers::ENTITE_TYPE_EVENEMENT, Fichiers::ENTITE_TYPE_REALISATION])->count(),
            'messages_importants' => Messages::where('type_message_id', 1)->count(),
            'annonces_actives' => Annonces::where('date_affichage', '<=', $now)
                ->whereRaw("(date_affichage + (nbr_jours_affichage || ' days')::interval) >= ?", [$now])
                ->count(),
        ];

        $audits = Audit::where('user_id', $user->id)
            ->latest()
            ->get();

        return view('admin.dashboards.content', compact('user', 'stats', 'audits', 'role'));
    }
}