<?php

namespace App\Models;

use App\Models\Base\Annonces as BaseAnnonces;
use OwenIt\Auditing\Contracts\Auditable;

class Annonces extends BaseAnnonces implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	protected $fillable = [
		'titre',
		'contenu',
		'date_affichage',
		'nbr_jours_affichage'
	];
}
