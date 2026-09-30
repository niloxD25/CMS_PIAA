<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Messages;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TypesMessage
 * 
 * @property int $id
 * @property string $type
 * @property string|null $deleted_at
 * 
 * @property Collection|Messages[] $messages
 *
 * @package App\Models\Base
 */
class TypesMessage extends Model
{
	use SoftDeletes;
	protected $table = 'types_message';
	public $timestamps = false;

	public function messages()
	{
		return $this->hasMany(Messages::class, 'type_message_id');
	}
}
