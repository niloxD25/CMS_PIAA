<?php

namespace App\Models;

use App\Models\Base\Realisations as BaseRealisations;
use OwenIt\Auditing\Contracts\Auditable;

class Realisations extends BaseRealisations implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	
	protected $fillable = [
		'titre',
		'beneficiaires',
		'description',
		'impacts_potentiel',
		'date_realisation',
		'image',
		'projet_id',
		'niveau_acces',
		'archived'
	];
}
