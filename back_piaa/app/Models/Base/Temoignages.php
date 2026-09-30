<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Projets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Temoignages
 * 
 * @property int $id
 * @property string|null $temoin
 * @property string|null $residence
 * @property string|null $contenu
 * @property string|null $image
 * @property int|null $projet_id
 * @property bool|null $archived
 * @property string|null $deleted_at
 * @property bool|null $est_important
 * 
 * @property Projets|null $projets
 *
 * @package App\Models\Base
 */
class Temoignages extends Model
{
	use SoftDeletes;
	protected $table = 'temoignages';
	public $timestamps = false;

	protected $casts = [
		'projet_id' => 'int',
		'archived' => 'bool',
		'est_important' => 'bool'
	];

	public function projets()
	{
		return $this->belongsTo(Projets::class, 'projet_id');
	}
}
