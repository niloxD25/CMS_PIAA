<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\OrganisationProjet;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TypesRelation
 * 
 * @property int $id
 * @property string $type
 * @property string|null $deleted_at
 * 
 * @property Collection|OrganisationProjet[] $organisation_projets
 *
 * @package App\Models\Base
 */
class TypesRelation extends Model
{
	use SoftDeletes;
	protected $table = 'types_relation';
	public $timestamps = false;

	public function organisation_projets()
	{
		return $this->hasMany(OrganisationProjet::class, 'type_relation_id');
	}
}
