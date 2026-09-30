<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\EtatEvenementController;
use App\Http\Controllers\OrganisationProjetController;
use App\Http\Controllers\RealisationController;
use App\Http\Controllers\TemoignageController;
use App\Http\Controllers\AnnonceController;
use App\Http\Controllers\FichierController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MessageController;
use App\Http\Middleware\EnsureApiAccessLevel;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// -------------------------
// Public routes
// -------------------------

// Health Check
Route::get('/ping', function () {
    $timezone = DB::selectOne("SHOW TIMEZONE");
    $currentTime = DB::selectOne("SELECT NOW() as current_time");

    return response()->json([
        'message'         => 'API PIAA OK',
        'timezone'        => config('app.timezone'),
        'current_time'    => now()->format('Y-m-d H:i:s'),
        'php_timezone'    => date_default_timezone_get(),
        'db_timezone'     => $timezone->TimeZone,
        'db_current_time' => $currentTime->current_time
    ]);
});

Route::post('/log-error', [App\Http\Controllers\LogController::class, 'store']);

// --- Auth publique ---
Route::post('login', [AuthController::class, 'login']);
Route::post('admin-login', [AuthController::class, 'adminLogin']);
Route::post('register', [AuthController::class, 'register']);

// --- Projets ---
Route::middleware([EnsureApiAccessLevel::class])->group(function () {
    Route::get('projets/others', [ProjetController::class, 'getOthersProjetsWithFullData']);
    Route::get('projets/actuelFullData', [ProjetController::class, 'getProjetActuelWithFullData']);

    Route::get('projets/actuel', [ProjetController::class, 'getProjetActuel']);
    Route::get('projets/{id}', [ProjetController::class, 'show']);

    // --- Événements ---
    Route::get('evenements/{id}/sections', [EvenementController::class, 'getSections']);
    Route::get('evenements', [EvenementController::class, 'index']);
    Route::get('evenements/{id}', [EvenementController::class, 'show']);

    // --- États d'événements ---
    Route::get('etatsEvenement', [EtatEvenementController::class, 'index']);
    Route::get('etatsEvenement/{id}', [EtatEvenementController::class, 'show']);

    // --- Annonces ---
    Route::get('annonces/actives', [AnnonceController::class, 'getAnnoncesActives']);
    Route::get('annonces', [AnnonceController::class, 'index']);
    Route::get('annonces/{id}', [AnnonceController::class, 'show']);

    // --- Réalisations ---
    Route::get('realisations', [RealisationController::class, 'index']);
    Route::get('realisations/{id}', [RealisationController::class, 'show']);

    // --- Témoignages ---
    Route::get('temoignages', [TemoignageController::class, 'index']);
    Route::get('temoignages/{id}', [TemoignageController::class, 'show']);

    // --- Fichiers ---
    Route::get('fichiers/{id}/download', [FichierController::class, 'download']);
    Route::get('fichiers', [FichierController::class, 'index']);
    Route::get('fichiers/{id}', [FichierController::class, 'show']);

    // --- Messages (public create) ---
    Route::post('messages', [MessageController::class, 'store']);

    // Associations / Organisations / Types de Relation
    Route::get('associations', [OrganisationProjetController::class, 'indexAssociations']);
    Route::get('associations/{id}', [OrganisationProjetController::class, 'showAssociation']);

    Route::get('organisations', [OrganisationProjetController::class, 'indexOrganisations']);
    Route::get('organisations/{id}', [OrganisationProjetController::class, 'showOrganisation']);

    Route::get('types-relation', [OrganisationProjetController::class, 'indexTypesRelation']);
    Route::get('types-relation/{id}', [OrganisationProjetController::class, 'showTypeRelation']);

});

// -------------------------
// Protected routes
// -------------------------
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // Users CRUD
    Route::apiResource('users', UserController::class)->except(['create', 'edit', 'store']);

    // Projets (POST, PUT, DELETE protégés)
    Route::post('projets/actuel', [ProjetController::class, 'setProjetActuel']);
    Route::apiResource('projets', ProjetController::class)->except(['show']);

    // Événements
    Route::apiResource('evenements', EvenementController::class)->except(['index', 'show']);
    Route::post('evenements/{id}/sections', [EvenementController::class, 'addSection']);
    Route::put('evenements/{id}/sections/{sectionId}', [EvenementController::class, 'updateSection']);
    Route::delete('evenements/{id}/sections/{sectionId}', [EvenementController::class, 'deleteSection']);

    // États Événement
    Route::apiResource('etatsEvenement', EtatEvenementController::class)->except(['index', 'show']);

    // Associations / Organisations / Types de Relation
    Route::post('associations', [OrganisationProjetController::class, 'storeAssociation']);
    Route::put('associations/{id}', [OrganisationProjetController::class, 'updateAssociation']);
    Route::delete('associations/{id}', [OrganisationProjetController::class, 'destroyAssociation']);

    Route::post('organisations', [OrganisationProjetController::class, 'storeOrganisation']);
    Route::put('organisations/{id}', [OrganisationProjetController::class, 'updateOrganisation']);
    Route::delete('organisations/{id}', [OrganisationProjetController::class, 'destroyOrganisation']);

    Route::post('types-relation', [OrganisationProjetController::class, 'storeTypeRelation']);
    Route::put('types-relation/{id}', [OrganisationProjetController::class, 'updateTypeRelation']);
    Route::delete('types-relation/{id}', [OrganisationProjetController::class, 'destroyTypeRelation']);

    // Réalisations
    Route::apiResource('realisations', RealisationController::class)->except(['index', 'show']);

    // Témoignages
    Route::apiResource('temoignages', TemoignageController::class)->except(['index', 'show']);

    // Annonces
    Route::apiResource('annonces', AnnonceController::class)->except(['index', 'show']);

    // Fichiers
    Route::apiResource('fichiers', FichierController::class)->except(['index', 'show']);

    // Types de Messages
    Route::get('messages/types', [MessageController::class, 'typesIndex']);
    Route::post('messages/types', [MessageController::class, 'typesStore']);
    Route::put('messages/types/{id}', [MessageController::class, 'typesUpdate']);
    Route::delete('messages/types/{id}', [MessageController::class, 'typesDestroy']);

    // Messages CRUD (backoffice)
    Route::get('messages', [MessageController::class, 'index']);
    Route::get('messages/{id}', [MessageController::class, 'show']);
    Route::put('messages/{id}', [MessageController::class, 'update']);
    Route::delete('messages/{id}', [MessageController::class, 'destroy']);
});
