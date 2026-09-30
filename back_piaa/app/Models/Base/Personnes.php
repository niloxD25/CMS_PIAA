<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\ProjetPersonnes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Personnes
 * 
 * @property int $id
 * @property string $nom
 * @property string|null $prenom
 * @property string|null $photo
 * @property string|null $poste
 * @property string|null $deleted_at
 * 
 * @property Collection|ProjetPersonnes[] $projet_personnes
 *
 * @package App\Models\Base
 */
class Personnes extends Model
{
	use SoftDeletes;
	protected $table = 'personnes';
	public $timestamps = false;

	public function projet_personnes()
	{
		return $this->hasMany(ProjetPersonnes::class, 'personne_id');
	}
}
