<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\TypesMessage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Messages
 * 
 * @property int $id
 * @property string $nom
 * @property string|null $mail
 * @property string|null $telephone
 * @property string $message
 * @property Carbon|null $date_envoi
 * @property int|null $type_message_id
 * @property bool|null $is_read
 * @property Carbon|null $read_at
 * @property string|null $deleted_at
 * 
 * @property TypesMessage|null $types_message
 *
 * @package App\Models\Base
 */
class Messages extends Model
{
	use SoftDeletes;
	protected $table = 'messages';
	public $timestamps = false;

	protected $casts = [
		'date_envoi' => 'datetime',
		'type_message_id' => 'int',
		'is_read' => 'bool',
		'read_at' => 'datetime'
	];

	public function types_message()
	{
		return $this->belongsTo(TypesMessage::class, 'type_message_id');
	}
}
