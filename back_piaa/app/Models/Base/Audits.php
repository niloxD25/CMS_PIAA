<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Audits
 * 
 * @property int $id
 * @property string|null $user_type
 * @property int|null $user_id
 * @property string $event
 * @property string $auditable_type
 * @property int $auditable_id
 * @property string|null $old_values
 * @property string|null $new_values
 * @property string|null $url
 * @property inet|null $ip_address
 * @property string|null $user_agent
 * @property string|null $tags
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models\Base
 */
class Audits extends Model
{
	protected $table = 'audits';

	protected $casts = [
		'user_id' => 'int',
		'auditable_id' => 'int',
		'ip_address' => 'inet'
	];
}
