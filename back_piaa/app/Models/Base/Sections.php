<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Evenements;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Sections
 * 
 * @property int $id
 * @property string|null $titre
 * @property string|null $description
 * @property string|null $image
 * @property int|null $evenement_id
 * @property string|null $deleted_at
 * 
 * @property Evenements|null $evenements
 *
 * @package App\Models\Base
 */
class Sections extends Model
{
	use SoftDeletes;
	protected $table = 'sections';
	public $timestamps = false;

	protected $casts = [
		'evenement_id' => 'int'
	];

	public function evenements()
	{
		return $this->belongsTo(Evenements::class, 'evenement_id');
	}
}
