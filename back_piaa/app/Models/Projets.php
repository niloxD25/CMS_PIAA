<?php

namespace App\Models;

use App\Models\Base\Projets as BaseProjets;
use OwenIt\Auditing\Contracts\Auditable;

class Projets extends BaseProjets implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $fillable = [
        'nom',
        'description',
        'devise',
        'acronymes',
        'adresse',
        'email',
        'telephone',
        'logo',
        'debut',
        'duree_annee',
        'archived'
    ];

    public const TYPE_PARTIE_PRENANTE = 1;
    public const TYPE_PARTENARIAT_FINANCIER = 2;
    
    public const NIVEAU_ACCES_DEFAUT = 999;

    /**
     * Relations simples
     */
    public function fichiers()
    {
        return $this->hasMany(Fichiers::class, 'projet_id')
            ->where('entite_type_id', Fichiers::ENTITE_TYPE_PROJET)
            ->where('archived', false);
    }

    public function actions_proximite()
    {
        return $this->hasMany(Fichiers::class, 'projet_id')
            ->where('entite_type_id', Fichiers::ENTITE_TYPE_ACTION_PROXIMITE)
            ->where('archived', false);
    }

    public function plansReperage()
    {
        return $this->hasMany(Fichiers::class, 'projet_id')
            ->where('entite_type_id', Fichiers::ENTITE_TYPE_PLAN_REPERAGE)
            ->where('archived', false);
    }

    public function partiesPrenantes()
    {
        return $this->hasMany(OrganisationProjet::class, 'projet_id')
            ->where('type_relation_id', self::TYPE_PARTIE_PRENANTE)
            ->with('organisations', 'types_relation');
    }

    public function partenariatsFinanciers()
    {
        return $this->hasMany(OrganisationProjet::class, 'projet_id')
            ->where('type_relation_id', self::TYPE_PARTENARIAT_FINANCIER)
            ->with('organisations', 'types_relation');
    }

    public function interviews()
    {
        return $this->hasMany(Fichiers::class, 'projet_id')
            ->where('entite_type_id', Fichiers::ENTITE_TYPE_INTERVIEW)
            ->where('archived', false);
    }

    public function projet_en_cours()
    {
        return $this->hasMany(ProjetEnCours::class, 'projet_id');
    }

    public function evenements()
    {
        return $this->hasMany(Evenements::class, 'projet_id')
            ->where('archived', false);
    }

    public function actualites()
    {
        return $this->hasMany(Evenements::class, 'projet_id')
            ->where('archived', false)
            ->where('est_actualite', true);
    }

    public function realisations()
    {
        return $this->hasMany(Realisations::class, 'projet_id')
            ->where('archived', false);
    }

    public function temoignages()
    {        
        return $this->hasMany(Temoignages::class, 'projet_id')
            ->where('est_important', false)
            ->where('archived', false);
    }

    public function mots()
    {        
        return $this->hasMany(Temoignages::class, 'projet_id')
            ->where('est_important', true)
            ->where('archived', false);
    }

    // Surcharge
    public function reseaux_sociaux()
    {
        return $this->belongsToMany(ReseauxSociaux::class, 'projets_reseaux_sociaux', 'projet_id', 'reseau_social_id')
                    ->withPivot('id', 'url', 'deleted_at')
                    ->wherePivotNull('deleted_at');
    }

    public function projet_apropos_sections()
	{
		return $this->hasMany(ProjetAproposSections::class, 'projet_id')->orderBy('ordre');
	}

    /**
     * Scope pour charger toutes les relations avec filtre niveauAcces
     */
    public function scopeWithAllRelations($query, $niveauAcces = null)
    {
        $niveauAcces = $niveauAcces ?? self::NIVEAU_ACCES_DEFAUT;
        \Log::info("Niveau accès final scope:", ['niveau_acces' => $niveauAcces]);
        
        return $query->with([
            'projet_apropos_sections',
            'fichiers' => fn ($q) => $q->where('niveau_acces', '>=', $niveauAcces)
                                      ->where('archived', false),
            'actions_proximite' => fn ($q) => $q->where('niveau_acces', '>=', $niveauAcces)
                                      ->where('archived', false),
            'plansReperage' => fn ($q) => $q->where('niveau_acces', '>=', $niveauAcces)
                                           ->where('archived', false),
            'partiesPrenantes',
            'partenariatsFinanciers',
            'evenements' => fn ($q) => $q->where('niveau_acces', '>=', $niveauAcces)
                                        ->where('archived', false)
                                        ->with(['etatCourant.etats_evenement']),
            'temoignages' => fn ($q) => $q->where('archived', false),
            'realisations' => fn ($q) => $q->where('niveau_acces', '>=', $niveauAcces)
                                          ->where('archived', false),
            'interviews' => fn ($q) => $q->where('niveau_acces', '>=', $niveauAcces)
                                        ->where('archived', false),
            'reseaux_sociaux',
            'actualites' => fn ($q) => $q->where('niveau_acces', '>=', $niveauAcces)
                                        ->where('archived', false)
                                        ->with(['etatCourant.etats_evenement']),
            'mots',
            'projet_personnes' => fn ($q) => $q->whereHas('personnes', function ($subQ) {
                                        $subQ->withoutTrashed();
                                    })
                                    ->with(['personnes' => function ($subQ) {
                                        $subQ->withoutTrashed();
                                    }])
                                    ->orderBy('hierarchie'),
            // 'projet_en_cours'
        ]);
    }

    /**
     * Méthode pour charger un projet avec un niveau d'accès spécifique
     */
    public function loadWithNiveauAcces($niveauAcces = null)
    {
        $niveauAcces = $niveauAcces ?? self::NIVEAU_ACCES_DEFAUT;
        \Log::info("Niveau accès final load:", ['niveau_acces' => $niveauAcces]);
        
        return $this->load([
            'projet_apropos_sections',
            'fichiers' => fn ($q) => $q->where('niveau_acces', '>=', $niveauAcces)
                                      ->where('archived', false),
            'actions_proximite' => fn ($q) => $q->where('niveau_acces', '>=', $niveauAcces)
                                      ->where('archived', false),                        
            'plansReperage' => fn ($q) => $q->where('niveau_acces', '>=', $niveauAcces)
                                           ->where('archived', false),
            'partiesPrenantes',
            'partenariatsFinanciers',
            'evenements' => fn ($q) => $q->where('niveau_acces', '>=', $niveauAcces)
                                        ->where('archived', false)
                                        ->with(['etatCourant.etats_evenement']),
            'temoignages' => fn ($q) => $q->where('archived', false),
            'realisations' => fn ($q) => $q->where('niveau_acces', '>=', $niveauAcces)
                                          ->where('archived', false),
            'interviews' => fn ($q) => $q->where('niveau_acces', '>=', $niveauAcces)
                                        ->where('archived', false),
            'reseaux_sociaux',
            'actualites' => fn ($q) => $q->where('niveau_acces', '>=', $niveauAcces)
                                        ->where('archived', false)
                                        ->with(['etatCourant.etats_evenement']),
            'mots',
            'projet_personnes' => fn ($q) => $q->whereHas('personnes', function ($subQ) {
                                        $subQ->withoutTrashed();
                                    })
                                    ->with(['personnes' => function ($subQ) {
                                        $subQ->withoutTrashed();
                                    }])
                                    ->orderBy('hierarchie'),
            // 'projet_en_cours'
        ]);
    }

    /**
     * Accesseur pour récupérer tous les fichiers d’un projet
     */
    public function getAllFichiersAttribute()
    {
        return Fichiers::where('projet_id', $this->id)
            ->whereIn('entite_type_id', [
                Fichiers::ENTITE_TYPE_PROJET,
                Fichiers::ENTITE_TYPE_PLAN_REPERAGE,
                Fichiers::ENTITE_TYPE_INTERVIEW
            ])
            ->where('archived', false)
            ->get();
    }

    /**
     * Méthode pour récupérer les éléments archivés
     */
    public function fichiersArchives()
    {
        return $this->hasMany(Fichiers::class, 'projet_id')
            ->where('entite_type_id', Fichiers::ENTITE_TYPE_PROJET)
            ->where('archived', true);
    }

    public function evenementsArchives()
    {
        return $this->hasMany(Evenements::class, 'projet_id')
            ->where('archived', true);
    }

    public function realisationsArchives()
    {
        return $this->hasMany(Realisations::class, 'projet_id')
            ->where('archived', true);
    }

    public function temoignagesArchives()
    {        
        return $this->hasMany(Temoignages::class, 'projet_id')
            ->where('archived', true);
    }
}