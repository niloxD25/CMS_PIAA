<?php

namespace App\Models;

use App\Models\Base\EtatsEvenement as BaseEtatsEvenement;
use OwenIt\Auditing\Contracts\Auditable;

class EtatsEvenement extends BaseEtatsEvenement implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	protected $fillable = [
		'etat'
	];
}
