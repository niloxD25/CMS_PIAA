<?php

namespace App\Models;

use App\Models\Base\EntitesFichier as BaseEntitesFichier;
use OwenIt\Auditing\Contracts\Auditable;

class EntitesFichier extends BaseEntitesFichier implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	protected $fillable = [
		'nom'
	];
}
