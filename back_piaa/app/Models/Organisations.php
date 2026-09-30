<?php

namespace App\Models;

use App\Models\Base\Organisations as BaseOrganisations;
use OwenIt\Auditing\Contracts\Auditable;

class Organisations extends BaseOrganisations implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	
	protected $fillable = [
		'nom',
		'description',
		'logo',
		'lien'
	];
}
