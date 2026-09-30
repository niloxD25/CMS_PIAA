<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Projets;
use App\Models\ReseauxSociaux;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ProjetsReseauxSociaux
 * 
 * @property int $id
 * @property int|null $projet_id
 * @property int|null $reseau_social_id
 * @property string $url
 * @property string|null $deleted_at
 * 
 * @property Projets|null $projets
 * @property ReseauxSociaux|null $reseaux_sociaux
 *
 * @package App\Models\Base
 */
class ProjetsReseauxSociaux extends Model
{
	use SoftDeletes;
	protected $table = 'projets_reseaux_sociaux';
	public $timestamps = false;

	protected $casts = [
		'projet_id' => 'int',
		'reseau_social_id' => 'int'
	];

	public function projets()
	{
		return $this->belongsTo(Projets::class, 'projet_id');
	}

	public function reseaux_sociaux()
	{
		return $this->belongsTo(ReseauxSociaux::class, 'reseau_social_id');
	}
}
