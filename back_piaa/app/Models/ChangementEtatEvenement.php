<?php

namespace App\Models;

use App\Models\Base\ChangementEtatEvenement as BaseChangementEtatEvenement;

class ChangementEtatEvenement extends BaseChangementEtatEvenement
{
	protected $fillable = [
		'evenement_id',
		'etat_evenement_id',
		'date'
	];
}
