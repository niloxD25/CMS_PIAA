<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserLogins
 * 
 * @property int $id
 * @property int $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $session_id
 * @property Carbon|null $logged_in_at
 * @property Carbon|null $logged_out_at
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $users
 *
 * @package App\Models\Base
 */
class UserLogins extends Model
{
	protected $table = 'user_logins';

	protected $casts = [
		'user_id' => 'int',
		'logged_in_at' => 'datetime',
		'logged_out_at' => 'datetime',
		'is_active' => 'bool'
	];

	public function users()
	{
		return $this->belongsTo(User::class, 'user_id');
	}
}
