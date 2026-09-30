<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CacheLocks
 * 
 * @property string $key
 * @property string $owner
 * @property int $expiration
 *
 * @package App\Models\Base
 */
class CacheLocks extends Model
{
	protected $table = 'cache_locks';
	protected $primaryKey = 'key';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'expiration' => 'int'
	];
}
