<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Annonces
 * 
 * @property int $id
 * @property string $titre
 * @property string|null $contenu
 * @property Carbon|null $date_affichage
 * @property int|null $nbr_jours_affichage
 *
 * @package App\Models\Base
 */
class Annonces extends Model
{
	protected $table = 'annonces';
	public $timestamps = false;

	protected $casts = [
		'date_affichage' => 'datetime',
		'nbr_jours_affichage' => 'int'
	];
}
