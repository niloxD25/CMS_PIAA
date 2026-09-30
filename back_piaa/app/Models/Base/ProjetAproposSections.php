<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Projets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ProjetAproposSections
 * 
 * @property int $id
 * @property int $projet_id
 * @property string|null $titre
 * @property string|null $sous_titre
 * @property string|null $description
 * @property int|null $ordre
 * @property string|null $image
 * @property string|null $deleted_at
 * 
 * @property Projets $projets
 *
 * @package App\Models\Base
 */
class ProjetAproposSections extends Model
{
	use SoftDeletes;
	protected $table = 'projet_apropos_sections';
	public $timestamps = false;

	protected $casts = [
		'projet_id' => 'int',
		'ordre' => 'int'
	];

	public function projets()
	{
		return $this->belongsTo(Projets::class, 'projet_id');
	}
}
