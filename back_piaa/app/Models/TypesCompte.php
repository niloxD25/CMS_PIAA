<?php

namespace App\Models;

use App\Models\Base\TypesCompte as BaseTypesCompte;
use OwenIt\Auditing\Contracts\Auditable;

class TypesCompte extends BaseTypesCompte implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	
	protected $fillable = [
		'type',
		'niveau_acces'
	];
}
