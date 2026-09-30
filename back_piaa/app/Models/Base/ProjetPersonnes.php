<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Personnes;
use App\Models\Projets;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ProjetPersonnes
 * 
 * @property int $id
 * @property int $projet_id
 * @property int $personne_id
 * @property int|null $hierarchie
 * @property Carbon|null $date_debut_participation
 * @property Carbon|null $date_fin_participation
 * 
 * @property Projets $projets
 * @property Personnes $personnes
 *
 * @package App\Models\Base
 */
class ProjetPersonnes extends Model
{
	protected $table = 'projet_personnes';
	public $timestamps = false;

	protected $casts = [
		'projet_id' => 'int',
		'personne_id' => 'int',
		'hierarchie' => 'int',
		'date_debut_participation' => 'datetime',
		'date_fin_participation' => 'datetime'
	];

	public function projets()
	{
		return $this->belongsTo(Projets::class, 'projet_id');
	}

	public function personnes()
	{
		return $this->belongsTo(Personnes::class, 'personne_id');
	}
}
