<?php

namespace App\Models;

use App\Models\Base\Cache as BaseCache;

class Cache extends BaseCache
{
	protected $fillable = [
		'value',
		'expiration'
	];
}
