<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\ChangementEtatEvenement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class EtatsEvenement
 * 
 * @property int $id
 * @property string $etat
 * @property string|null $deleted_at
 * 
 * @property Collection|ChangementEtatEvenement[] $changement_etat_evenements
 *
 * @package App\Models\Base
 */
class EtatsEvenement extends Model
{
	use SoftDeletes;
	protected $table = 'etats_evenement';
	public $timestamps = false;

	public function changement_etat_evenements()
	{
		return $this->hasMany(ChangementEtatEvenement::class, 'etat_evenement_id');
	}
}
