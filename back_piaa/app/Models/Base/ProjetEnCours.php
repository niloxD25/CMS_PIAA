<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Projets;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ProjetEnCours
 * 
 * @property int $id
 * @property int|null $projet_id
 * @property Carbon $date_changement
 * 
 * @property Projets|null $projets
 *
 * @package App\Models\Base
 */
class ProjetEnCours extends Model
{
	protected $table = 'projet_en_cours';
	public $timestamps = false;

	protected $casts = [
		'projet_id' => 'int',
		'date_changement' => 'datetime'
	];

	public function projets()
	{
		return $this->belongsTo(Projets::class, 'projet_id');
	}
}
