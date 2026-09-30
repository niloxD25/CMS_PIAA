<?php

namespace App\Models;

use App\Models\Base\ProjetsReseauxSociaux as BaseProjetsReseauxSociaux;
use OwenIt\Auditing\Contracts\Auditable;

class ProjetsReseauxSociaux extends BaseProjetsReseauxSociaux implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	
	protected $fillable = [
		'url',
		'projet_id',
		'reseau_social_id'
	];
}
