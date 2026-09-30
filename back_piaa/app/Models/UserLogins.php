<?php

namespace App\Models;

use App\Models\Base\UserLogins as BaseUserLogins;

class UserLogins extends BaseUserLogins
{
	public const TYPE_API   = 'api';
    public const TYPE_ADMIN = 'admin';
    public const TYPE_WEB   = 'web';
	
	protected $fillable = [
		'user_id',
		'ip_address',
		'user_agent',
		'session_id',
		'connection_type',
		'logged_in_at',
		'logged_out_at',
		'is_active'
	];
}
