<?php

namespace App\Models;

use App\Models\Base\Sessions as BaseSessions;

class Sessions extends BaseSessions
{
	protected $fillable = [
		'user_id',
		'ip_address',
		'user_agent',
		'payload',
		'last_activity'
	];
}
