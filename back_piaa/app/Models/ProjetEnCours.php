<?php

namespace App\Models;

use App\Models\Base\ProjetEnCours as BaseProjetEnCours;
use OwenIt\Auditing\Contracts\Auditable;

class ProjetEnCours extends BaseProjetEnCours implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	
	protected $fillable = [
		'projet_id',
		'date_changement'
	];
}
