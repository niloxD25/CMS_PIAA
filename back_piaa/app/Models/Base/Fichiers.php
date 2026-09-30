<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\EntitesFichier;
use App\Models\Projets;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Fichiers
 * 
 * @property int $id
 * @property string|null $titre
 * @property string|null $description
 * @property string|null $type_fichier
 * @property string $chemin
 * @property Carbon|null $date_upload
 * @property int $entite_type_id
 * @property int|null $entite_id
 * @property int|null $niveau_acces
 * @property int|null $projet_id
 * @property bool|null $archived
 * @property string|null $deleted_at
 * 
 * @property EntitesFichier $entites_fichier
 * @property Projets|null $projets
 *
 * @package App\Models\Base
 */
class Fichiers extends Model
{
	use SoftDeletes;
	protected $table = 'fichiers';
	public $timestamps = false;

	protected $casts = [
		'date_upload' => 'datetime',
		'entite_type_id' => 'int',
		'entite_id' => 'int',
		'niveau_acces' => 'int',
		'projet_id' => 'int',
		'archived' => 'bool'
	];

	public function entites_fichier()
	{
		return $this->belongsTo(EntitesFichier::class, 'entite_type_id');
	}

	public function projets()
	{
		return $this->belongsTo(Projets::class, 'projet_id');
	}
}
