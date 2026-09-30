<?php

namespace App\Models;

use App\Models\Base\CacheLocks as BaseCacheLocks;

class CacheLocks extends BaseCacheLocks
{
	protected $fillable = [
		'owner',
		'expiration'
	];
}
