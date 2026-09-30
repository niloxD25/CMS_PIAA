<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Fichiers;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class EntitesFichier
 * 
 * @property int $id
 * @property string $nom
 * @property string|null $deleted_at
 * 
 * @property Collection|Fichiers[] $fichiers
 *
 * @package App\Models\Base
 */
class EntitesFichier extends Model
{
	use SoftDeletes;
	protected $table = 'entites_fichier';
	public $timestamps = false;

	public function fichiers()
	{
		return $this->hasMany(Fichiers::class, 'entite_type_id');
	}
}
