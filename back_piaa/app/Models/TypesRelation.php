<?php

namespace App\Models;

use App\Models\Base\TypesRelation as BaseTypesRelation;
use OwenIt\Auditing\Contracts\Auditable;

class TypesRelation extends BaseTypesRelation implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	
	protected $fillable = [
		'type'
	];
}
