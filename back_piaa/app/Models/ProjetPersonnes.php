<?php

namespace App\Models;

use App\Models\Base\ProjetPersonnes as BaseProjetPersonnes;
use OwenIt\Auditing\Contracts\Auditable;

class ProjetPersonnes extends BaseProjetPersonnes implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	
	protected $fillable = [
		'projet_id',
		'personne_id',
		'hierarchie',
		'date_debut_participation',
		'date_fin_participation'
	];
}
