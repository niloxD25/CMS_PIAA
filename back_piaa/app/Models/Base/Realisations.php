<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Projets;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Realisations
 * 
 * @property int $id
 * @property string|null $titre
 * @property string|null $beneficiaires
 * @property string|null $description
 * @property string|null $impacts_potentiel
 * @property Carbon|null $date_realisation
 * @property string|null $image
 * @property int|null $projet_id
 * @property int|null $niveau_acces
 * @property bool|null $archived
 * @property string|null $deleted_at
 * 
 * @property Projets|null $projets
 *
 * @package App\Models\Base
 */
class Realisations extends Model
{
	use SoftDeletes;
	protected $table = 'realisations';
	public $timestamps = false;

	protected $casts = [
		'date_realisation' => 'datetime',
		'projet_id' => 'int',
		'niveau_acces' => 'int',
		'archived' => 'bool'
	];

	public function projets()
	{
		return $this->belongsTo(Projets::class, 'projet_id');
	}
}
