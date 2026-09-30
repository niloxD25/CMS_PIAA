<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Organisations;
use App\Models\Projets;
use App\Models\TypesRelation;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrganisationProjet
 * 
 * @property int $id
 * @property int|null $organisation_id
 * @property int|null $projet_id
 * @property int|null $type_relation_id
 * 
 * @property Organisations|null $organisations
 * @property Projets|null $projets
 * @property TypesRelation|null $types_relation
 *
 * @package App\Models\Base
 */
class OrganisationProjet extends Model
{
	protected $table = 'organisation_projet';
	public $timestamps = false;

	protected $casts = [
		'organisation_id' => 'int',
		'projet_id' => 'int',
		'type_relation_id' => 'int'
	];

	public function organisations()
	{
		return $this->belongsTo(Organisations::class, 'organisation_id');
	}

	public function projets()
	{
		return $this->belongsTo(Projets::class, 'projet_id');
	}

	public function types_relation()
	{
		return $this->belongsTo(TypesRelation::class, 'type_relation_id');
	}
}
