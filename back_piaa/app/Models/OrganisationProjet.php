<?php

namespace App\Models;

use App\Models\Base\OrganisationProjet as BaseOrganisationProjet;
use OwenIt\Auditing\Contracts\Auditable;

class OrganisationProjet extends BaseOrganisationProjet implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	
	protected $fillable = [
		'organisation_id',
		'projet_id',
		'type_relation_id'
	];
}
