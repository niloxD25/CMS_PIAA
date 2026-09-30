<?php

namespace App\Models;

use App\Models\Base\Evenements as BaseEvenements;
use App\Models\Fichiers;
use OwenIt\Auditing\Contracts\Auditable;

class Evenements extends BaseEvenements implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	protected $fillable = [
		'titre',
		'description',
		'date_debut',
		'date_fin',
		'image',
		'projet_id',
		'niveau_acces',
		'est_actualite',
		'archived'
	];

	// Dernier état (le plus récent)
    public function etatCourant()
    {
        return $this->hasOne(ChangementEtatEvenement::class, 'evenement_id')
                    ->latestOfMany();
    }

	// Tous les fichiers liés à l'évènement
	public function fichiers()
	{
		return $this->hasMany(Fichiers::class, 'entite_id')
					->where('entite_type_id', Fichiers::ENTITE_TYPE_EVENEMENT);
	}

}
