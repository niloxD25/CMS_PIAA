<?php

namespace App\Models;

use App\Models\Base\ProjetAproposSections as BaseProjetAproposSections;
use OwenIt\Auditing\Contracts\Auditable;

class ProjetAproposSections extends BaseProjetAproposSections implements Auditable
{
	use \OwenIt\Auditing\Auditable;

	protected $fillable = [
		'projet_id',
		'titre',
		'sous_titre',
		'description',
		'ordre',
		'image'
	];
}
