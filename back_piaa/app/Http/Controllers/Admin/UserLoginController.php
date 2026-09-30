<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserLogins;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class UserLoginController extends Controller
{
    /**
     * Liste des connexions
     * GET /admin/user-logins
     */
    public function index(Request $request)
    {
        $perPage   = $request->get('per_page', 10);
        $sortBy    = $request->get('sort_by', 'logged_in_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // Colonnes autorisées pour le tri
        $allowedSortColumns = [
            'logged_in_at',
            'logged_out_at',
            'last_activity_at',
            'connection_type',
            'is_active',
            'user'
        ];

        $allowedSortOrders = ['asc', 'desc'];

        $sortBy    = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'logged_in_at';
        $sortOrder = in_array($sortOrder, $allowedSortOrders) ? $sortOrder : 'desc';

        $query = UserLogins::with('users');

        $filters = $request->only([
            'user_id',
            'connection_type',
            'is_active'
        ]);

        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $query->where($key, $value);
            }
        }

        if ($sortBy === 'user') {
            $query->leftJoin('users', 'user_logins.user_id', '=', 'users.id')
                ->select('user_logins.*')
                ->orderBy('users.name', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $logins = $query->paginate($perPage);

        // Données pour les filtres
        $users = User::orderBy('name')->get();

        $connectionTypes = UserLogins::distinct()
            ->pluck('connection_type')
            ->filter();

        // Conserver les paramètres dans la pagination
        $logins->appends([
            'per_page'   => $perPage,
            'sort_by'    => $sortBy,
            'sort_order' => $sortOrder,
        ] + $filters);

        return view(
            'admin.user_logins.index',
            compact(
                'logins',
                'users',
                'connectionTypes',
                'sortBy',
                'sortOrder'
            )
        );
    }


    /**
     * Détails d'une connexion
     */
    public function show($id)
    {
        $login = UserLogins::with('users')->find($id);

        if (!$login) {
            return redirect()
                ->route('admin.user-logins.index')
                ->with('error', 'Connexion introuvable');
        }

        return view('admin.user_logins.show', compact('login'));
    }

    /**
     * Forcer la déconnexion d'une session
     */
    public function destroy($id)
    {
        $login = UserLogins::find($id);

        if (!$login || !$login->is_active) {
            return redirect()
                ->back()
                ->with('error', 'Session déjà inactive');
        }

        /**
         *  API (Sanctum)
         */
        if ($login->connection_type === UserLogins::TYPE_API) {
            PersonalAccessToken::where('id', $login->session_id)->delete();
        }

        /**
         *  WEB / ADMIN (session Laravel)
         */
        if (in_array($login->connection_type, [UserLogins::TYPE_ADMIN, UserLogins::TYPE_WEB])) {
            DB::table('sessions')
                ->where('id', $login->session_id)
                ->delete();
        }

        $login->update([
            'is_active'     => false,
            'logged_out_at' => now(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Session déconnectée avec succès');
    }

    /**
     * Forcer la déconnexion de toutes les sessions d'un utilisateur
     */
    public function destroyUserSessions($userId)
    {
        $logins = UserLogins::where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        foreach ($logins as $login) {
            // API (Sanctum)
            if ($login->connection_type === UserLogins::TYPE_API) {
                PersonalAccessToken::where('id', $login->session_id)->delete();
            }

            // WEB / ADMIN (session Laravel)
            if (in_array($login->connection_type, [UserLogins::TYPE_ADMIN, UserLogins::TYPE_WEB])) {
                DB::table('sessions')
                    ->where('id', $login->session_id)
                    ->delete();
            }

            $login->update([
                'is_active'     => false,
                'logged_out_at' => now(),
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Toutes les sessions ont été fermées');
    }

    /**
     * Désactiver toutes les sessions devenues inactives (cleanup)
     */
    public function cleanupInactiveSessions()
    {
        $logins = UserLogins::where('is_active', true)->get();

        $disabledCount = 0;

        foreach ($logins as $login) {
            $isStillValid = true;

            /**
             * API (Sanctum)
             */
            if ($login->connection_type === UserLogins::TYPE_API) {
                $tokenExists = PersonalAccessToken::where('id', $login->session_id)->exists();
                $isStillValid = $tokenExists;
            }

            /**
             * WEB / ADMIN
             */
            if (in_array($login->connection_type, [UserLogins::TYPE_WEB, UserLogins::TYPE_ADMIN])) {
                $sessionExists = DB::table('sessions')
                    ->where('id', $login->session_id)
                    ->exists();

                $isStillValid = $sessionExists;
            }

            /**
             * Désactivation
             */
            if (! $isStillValid) {
                $login->update([
                    'is_active'     => false,
                    'logged_out_at' => now(),
                ]);

                $disabledCount++;
            }
        }

        return redirect()
            ->back()
            ->with(
                'success',
                "{$disabledCount} session(s) inactive(s) ont été désactivées"
            );
    }

}