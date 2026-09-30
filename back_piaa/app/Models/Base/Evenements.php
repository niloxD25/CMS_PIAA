<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\ChangementEtatEvenement;
use App\Models\Projets;
use App\Models\Sections;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Evenements
 * 
 * @property int $id
 * @property string $titre
 * @property string|null $description
 * @property Carbon|null $date_debut
 * @property Carbon|null $date_fin
 * @property string|null $image
 * @property int|null $projet_id
 * @property int|null $niveau_acces
 * @property bool|null $archived
 * @property string|null $deleted_at
 * @property bool|null $est_actualite
 * 
 * @property Projets|null $projets
 * @property Collection|ChangementEtatEvenement[] $changement_etat_evenements
 * @property Collection|Sections[] $sections
 *
 * @package App\Models\Base
 */
class Evenements extends Model
{
	use SoftDeletes;
	protected $table = 'evenements';
	public $timestamps = false;

	protected $casts = [
		'date_debut' => 'datetime',
		'date_fin' => 'datetime',
		'projet_id' => 'int',
		'niveau_acces' => 'int',
		'archived' => 'bool',
		'est_actualite' => 'bool'
	];

	public function projets()
	{
		return $this->belongsTo(Projets::class, 'projet_id');
	}

	public function changement_etat_evenements()
	{
		return $this->hasMany(ChangementEtatEvenement::class, 'evenement_id');
	}

	public function sections()
	{
		return $this->hasMany(Sections::class, 'evenement_id');
	}
}
