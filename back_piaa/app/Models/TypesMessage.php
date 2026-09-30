<?php

namespace App\Models;

use App\Models\Base\TypesMessage as BaseTypesMessage;
use OwenIt\Auditing\Contracts\Auditable;

class TypesMessage extends BaseTypesMessage implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	
	protected $fillable = [
		'type'
	];
}
