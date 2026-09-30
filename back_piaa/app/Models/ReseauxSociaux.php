<?php

namespace App\Models;

use App\Models\Base\ReseauxSociaux as BaseReseauxSociaux;
use OwenIt\Auditing\Contracts\Auditable;

class ReseauxSociaux extends BaseReseauxSociaux implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	
	protected $fillable = [
		'nom',
		'icone'
	];
}
