<?php

namespace App\Models;

use App\Models\Base\Jobs as BaseJobs;

class Jobs extends BaseJobs
{
	protected $fillable = [
		'queue',
		'payload',
		'attempts',
		'reserved_at',
		'available_at'
	];
}
