<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\EtatsEvenement;
use App\Models\Evenements;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ChangementEtatEvenement
 * 
 * @property int $id
 * @property int|null $evenement_id
 * @property int|null $etat_evenement_id
 * @property Carbon $date
 * 
 * @property Evenements|null $evenements
 * @property EtatsEvenement|null $etats_evenement
 *
 * @package App\Models\Base
 */
class ChangementEtatEvenement extends Model
{
	protected $table = 'changement_etat_evenement';
	public $timestamps = false;

	protected $casts = [
		'evenement_id' => 'int',
		'etat_evenement_id' => 'int',
		'date' => 'datetime'
	];

	public function evenements()
	{
		return $this->belongsTo(Evenements::class, 'evenement_id');
	}

	public function etats_evenement()
	{
		return $this->belongsTo(EtatsEvenement::class, 'etat_evenement_id');
	}
}
