<?php

namespace App\Models;

use App\Models\Base\Personnes as BasePersonnes;
use OwenIt\Auditing\Contracts\Auditable;

class Personnes extends BasePersonnes implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	
	protected $fillable = [
		'nom',
		'prenom',
		'photo',
		'poste'
	];

	public function projets()
    {
        return $this->belongsToMany(Projets::class, 'projet_personnes', 'personne_id', 'projet_id')
                    ->withPivot('date_debut_participation', 'date_fin_participation', 'hierarchie', 'id');
    }
}
