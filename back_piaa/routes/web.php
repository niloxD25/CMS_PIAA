<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\LogWebSession;
use App\Http\Middleware\EnsureSessionIsActive;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProjetController;
use App\Http\Controllers\Admin\AnnonceController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\OrganisationController;
use App\Http\Controllers\Admin\TemoignageController;
use App\Http\Controllers\Admin\EvenementController;
use App\Http\Controllers\Admin\FichierController;
use App\Http\Controllers\Admin\RealisationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TypesCompteController;
use App\Http\Controllers\Admin\ReseauSocialController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\PersonneController;
use App\Http\Controllers\Admin\ProjetPersonneController;
use App\Http\Controllers\Admin\ProjetAproposSectionsController;
use App\Http\Controllers\Admin\UserLoginController;
use Illuminate\Contracts\Cache\Lock;

Route::get('/', function () {
    return view('welcome');
});

// Routes publiques
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');

// Routes protégées
Route::middleware(['web', 'auth', LogWebSession::class, EnsureSessionIsActive::class])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
    Route::get('/content-dashboard', [DashboardController::class, 'contentDashboard'])->name('content.dashboard');
    
    Route::get('/', [DashboardController::class, 'redirectToDashboard'])->name('home');

    // Routes pour la gestion des projets
    Route::prefix('projets')->name('projets.')->group(function () {
        Route::get('/', [ProjetController::class, 'index'])->name('index');
        Route::get('/create', [ProjetController::class, 'create'])->name('create');
        Route::post('/', [ProjetController::class, 'store'])->name('store');
        Route::get('/{id}', [ProjetController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ProjetController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ProjetController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProjetController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/set-actuel', [ProjetController::class, 'setProjetActuel'])->name('set-actuel');
        Route::post('/{id}/toggle-archive', [ProjetController::class, 'toggleArchive'])->name('toggle-archive');
    });

    // Routes pour la gestion des annonces
    Route::prefix('annonces')->name('annonces.')->group(function () {
        Route::get('/', [AnnonceController::class, 'index'])->name('index');
        Route::get('/create', [AnnonceController::class, 'create'])->name('create');
        Route::post('/', [AnnonceController::class, 'store'])->name('store');
        Route::get('/{id}', [AnnonceController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AnnonceController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AnnonceController::class, 'update'])->name('update');
        Route::delete('/{id}', [AnnonceController::class, 'destroy'])->name('destroy');
    });

    // Routes pour la gestion des messages
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('/{id}', [MessageController::class, 'show'])->name('show');
        Route::delete('/{id}', [MessageController::class, 'destroy'])->name('destroy');
        Route::put('/{id}/update-type', [MessageController::class, 'updateType'])->name('update-type');
    });

    // Routes pour les types de messages
    Route::prefix('types-messages')->name('types-messages.')->group(function () {
        Route::get('/', [MessageController::class, 'typesIndex'])->name('index');
        Route::get('/create', [MessageController::class, 'typesCreate'])->name('create');
        Route::post('/', [MessageController::class, 'typesStore'])->name('store');
        Route::get('/{id}/edit', [MessageController::class, 'typesEdit'])->name('edit');
        Route::put('/{id}', [MessageController::class, 'typesUpdate'])->name('update');
        Route::delete('/{id}', [MessageController::class, 'typesDestroy'])->name('destroy');
    });

    // Routes pour la gestion des organisations
    Route::prefix('organisations')->name('organisations.')->group(function () {
        // Associations organisation-projet
        Route::prefix('associations')->name('associations.')->group(function () {
            Route::get('/', [OrganisationController::class, 'indexAssociations'])->name('index');
            Route::get('/create', [OrganisationController::class, 'createAssociation'])->name('create');
            Route::post('/', [OrganisationController::class, 'storeAssociation'])->name('store');
            Route::get('/{id}', [OrganisationController::class, 'showAssociation'])->name('show');
            Route::get('/{id}/edit', [OrganisationController::class, 'editAssociation'])->name('edit');
            Route::put('/{id}', [OrganisationController::class, 'updateAssociation'])->name('update');
            Route::delete('/{id}', [OrganisationController::class, 'destroyAssociation'])->name('destroy');
        });

        // Types de relation
        Route::prefix('types-relation')->name('types-relation.')->group(function () {
            Route::get('/', [OrganisationController::class, 'indexTypesRelation'])->name('index');
            Route::get('/create', [OrganisationController::class, 'createTypeRelation'])->name('create');
            Route::post('/', [OrganisationController::class, 'storeTypeRelation'])->name('store');
            Route::get('/{id}/edit', [OrganisationController::class, 'editTypeRelation'])->name('edit');
            Route::put('/{id}', [OrganisationController::class, 'updateTypeRelation'])->name('update');
            Route::delete('/{id}', [OrganisationController::class, 'destroyTypeRelation'])->name('destroy');
        });

        // Organisations
        Route::get('/', [OrganisationController::class, 'index'])->name('index');
        Route::get('/create', [OrganisationController::class, 'create'])->name('create');
        Route::post('/', [OrganisationController::class, 'store'])->name('store');
        Route::get('/{id}', [OrganisationController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [OrganisationController::class, 'edit'])->name('edit');
        Route::put('/{id}', [OrganisationController::class, 'update'])->name('update');
        Route::delete('/{id}', [OrganisationController::class, 'destroy'])->name('destroy');
    });

    // Routes pour la gestion des temoignages
    Route::prefix('temoignages')->name('temoignages.')->group(function () {
        Route::get('/', [TemoignageController::class, 'index'])->name('index');
        Route::get('/create', [TemoignageController::class, 'create'])->name('create');
        Route::post('/', [TemoignageController::class, 'store'])->name('store');
        Route::get('/{id}', [TemoignageController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [TemoignageController::class, 'edit'])->name('edit');
        Route::put('/{id}', [TemoignageController::class, 'update'])->name('update');
        Route::delete('/{id}', [TemoignageController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-important', [TemoignageController::class, 'toggleImportant'])->name('toggle-important');
        Route::post('/{id}/toggle-archive', [TemoignageController::class, 'toggleArchive'])->name('toggle-archive');
    });

    // Routes pour la gestion des évènements et des sections
    Route::prefix('evenements/{evenement}/sections')->name('evenements.sections.')->group(function () {
        Route::post('/', [EvenementController::class, 'storeSection'])->name('store');
        Route::get('/{section}', [EvenementController::class, 'showSection'])->name('show');
        Route::put('/{section}', [EvenementController::class, 'updateSection'])->name('update');
        Route::delete('/{section}', [EvenementController::class, 'destroySection'])->name('destroy');
    });

    Route::prefix('evenements')->name('evenements.')->group(function () {
        Route::get('/', [EvenementController::class, 'index'])->name('index');
        Route::get('/create', [EvenementController::class, 'create'])->name('create');
        Route::post('/fichiers', [EvenementController::class, 'storeFichier'])->name('storeFichier');
        Route::get('/fichiers/{id}', [EvenementController::class, 'showFichier'])->name('showFichier');
        Route::post('/', [EvenementController::class, 'store'])->name('store');
        Route::get('/{id}', [EvenementController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [EvenementController::class, 'edit'])->name('edit');
        Route::put('/{id}', [EvenementController::class, 'update'])->name('update');
        Route::delete('/{id}', [EvenementController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-actualite', [EvenementController::class, 'toggleActualite'])->name('toggle-actualite');
        Route::post('/{id}/toggle-archive', [EvenementController::class, 'toggleArchive'])->name('toggle-archive');
    });

    // Routes pour la gestion des fichiers
    Route::prefix('fichiers')->name('fichiers.')->group(function () {
        Route::get('/', [FichierController::class, 'index'])->name('index');
        Route::get('/create', [FichierController::class, 'create'])->name('create');
        Route::post('/', [FichierController::class, 'store'])->name('store');
        Route::get('/{id}', [FichierController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [FichierController::class, 'edit'])->name('edit');
        Route::put('/{id}', [FichierController::class, 'update'])->name('update');
        Route::delete('/{id}', [FichierController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/download', [FichierController::class, 'download'])->name('download');
        Route::post('/{id}/toggle-archive', [FichierController::class, 'toggleArchive'])->name('toggle-archive');
    });

    // Routes pour la gestion des réalisations
    Route::prefix('realisations')->name('realisations.')->group(function () {
        Route::get('/', [RealisationController::class, 'index'])->name('index');
        Route::get('/create', [RealisationController::class, 'create'])->name('create');
        Route::post('/', [RealisationController::class, 'store'])->name('store');
        Route::get('/{id}', [RealisationController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [RealisationController::class, 'edit'])->name('edit');
        Route::put('/{id}', [RealisationController::class, 'update'])->name('update');
        Route::delete('/{id}', [RealisationController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-archive', [RealisationController::class, 'toggleArchive'])->name('toggle-archive');
    });

    // Routes pour les utilisateurs
    Route::prefix('users')->name('users.')->group(function () {
        // Routes pour les types de compte
        Route::prefix('types-compte')->name('types-compte.')->group(function () {
            Route::get('/', [TypesCompteController::class, 'index'])->name('index');
            Route::get('/create', [TypesCompteController::class, 'create'])->name('create');
            Route::post('/', [TypesCompteController::class, 'store'])->name('store');
            Route::get('/{id}', [TypesCompteController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [TypesCompteController::class, 'edit'])->name('edit');
            Route::put('/{id}', [TypesCompteController::class, 'update'])->name('update');
            Route::delete('/{id}', [TypesCompteController::class, 'destroy'])->name('destroy');
        });
        
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{id}', [UserController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Routes pour les audits
    Route::prefix('audits')->name('audits.')->group(function () {
        Route::get('/', [AuditController::class, 'index'])->name('index');
        Route::get('/{id}', [AuditController::class, 'show'])->name('show');
        Route::post('/{audit}/restore', [AuditController::class, 'restore'])->name('restore');
        Route::delete('/{audit}/force-delete', [AuditController::class, 'forceDelete'])->name('forceDelete');
    });

    // Routes pour la gestion des réseaux sociaux
    Route::prefix('reseaux-sociaux')->name('reseaux-sociaux.')->group(function () {
        Route::get('/', [ReseauSocialController::class, 'index'])->name('index');
        Route::get('/create', [ReseauSocialController::class, 'create'])->name('create');
        Route::post('/', [ReseauSocialController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [ReseauSocialController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ReseauSocialController::class, 'update'])->name('update');
        Route::delete('/{id}', [ReseauSocialController::class, 'destroy'])->name('destroy');
    });

    // Routes pour les relations projets - réseaux sociaux
    Route::prefix('projets/{projet}/reseaux-sociaux')->name('projets.reseaux-sociaux.')->group(function () {
        Route::post('/', [ReseauSocialController::class, 'addToProjet'])->name('store');
        Route::post('/{reseauSocial}', [ReseauSocialController::class, 'updateInProjet'])->name('update');
        Route::delete('/{reseauSocial}', [ReseauSocialController::class, 'removeFromProjet'])->name('destroy');
    });

    // Routes pour les relations projets - personnes
    Route::prefix('projets/{projetId}/participants')->name('projets.participants.')->group(function () {
        Route::get('/', [ProjetPersonneController::class, 'index'])->name('index');
        Route::get('/create', [ProjetPersonneController::class, 'create'])->name('create');
        Route::post('/', [ProjetPersonneController::class, 'store'])->name('store');
        Route::get('/{id}', [ProjetPersonneController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ProjetPersonneController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ProjetPersonneController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProjetPersonneController::class, 'destroy'])->name('destroy');
    });

    // Routes pour les à propos des projets
    Route::prefix('projets/{projetId}/apropos-sections')->name('projets.apropos-sections.')->group(function () {
        Route::get('/', [ProjetAproposSectionsController::class, 'index'])->name('index');
        Route::get('/create', [ProjetAproposSectionsController::class, 'create'])->name('create');
        Route::post('/', [ProjetAproposSectionsController::class, 'store'])->name('store');
        Route::get('/{id}', [ProjetAproposSectionsController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ProjetAproposSectionsController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ProjetAproposSectionsController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProjetAproposSectionsController::class, 'destroy'])->name('destroy');
        Route::post('/reorder', [ProjetAproposSectionsController::class, 'reorder'])->name('reorder');
    });

    // Routes pour la gestion des personnes
    Route::prefix('personnes')->name('personnes.')->group(function () {
        Route::get('/', [PersonneController::class, 'index'])->name('index');
        Route::get('/create', [PersonneController::class, 'create'])->name('create');
        Route::post('/', [PersonneController::class, 'store'])->name('store');
        Route::get('/{id}', [PersonneController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PersonneController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PersonneController::class, 'update'])->name('update');
        Route::delete('/{id}', [PersonneController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('user-logins')->name('user-logins.')->group(function () {
        Route::get('/', [UserLoginController::class, 'index'])->name('index');
        Route::get('/{id}', [UserLoginController::class, 'show'])->name('show');
        Route::delete('/{id}', [UserLoginController::class, 'destroy'])->name('destroy');
        Route::delete('/user/{userId}/sessions', [UserLoginController::class, 'destroyUserSessions'])->name('destroy-user');
        Route::post('/cleanup',[UserLoginController::class, 'cleanupInactiveSessions'])->name('cleanup');
    });

});