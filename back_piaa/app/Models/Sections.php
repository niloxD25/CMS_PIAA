<?php

namespace App\Models;

use App\Models\Base\Sections as BaseSections;
use OwenIt\Auditing\Contracts\Auditable;

class Sections extends BaseSections implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	
	protected $fillable = [
		'titre',
		'description',
		'image',
		'evenement_id'
	];
}
