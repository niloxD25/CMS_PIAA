<?php

namespace App\Models;

use App\Models\Base\Audits as BaseAudits;

class Audits extends BaseAudits
{
	protected $fillable = [
		'user_type',
		'user_id',
		'event',
		'auditable_type',
		'auditable_id',
		'old_values',
		'new_values',
		'url',
		'ip_address',
		'user_agent',
		'tags'
	];
}
