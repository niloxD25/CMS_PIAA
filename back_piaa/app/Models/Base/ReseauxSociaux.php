<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Projets;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ReseauxSociaux
 * 
 * @property int $id
 * @property string|null $nom
 * @property string|null $icone
 * @property string|null $deleted_at
 * 
 * @property Collection|Projets[] $projets
 *
 * @package App\Models\Base
 */
class ReseauxSociaux extends Model
{
	use SoftDeletes;
	protected $table = 'reseaux_sociaux';
	public $timestamps = false;

	public function projets()
	{
		return $this->belongsToMany(Projets::class, 'projets_reseaux_sociaux', 'reseau_social_id', 'projet_id')
					->withPivot('id', 'url', 'deleted_at');
	}
}
