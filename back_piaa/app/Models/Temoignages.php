<?php

namespace App\Models;

use App\Models\Base\Temoignages as BaseTemoignages;
use OwenIt\Auditing\Contracts\Auditable;

class Temoignages extends BaseTemoignages implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	
	protected $fillable = [
		'temoin',
		'residence',
		'contenu',
		'image',
		'projet_id',
		'est_important',
		'archived'
	];
}
