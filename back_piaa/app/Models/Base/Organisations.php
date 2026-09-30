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
 * Class Organisations
 * 
 * @property int $id
 * @property string $nom
 * @property string|null $description
 * @property string|null $logo
 * @property string|null $lien
 * @property string|null $deleted_at
 * 
 * @property Collection|OrganisationProjet[] $organisation_projets
 *
 * @package App\Models\Base
 */
class Organisations extends Model
{
	use SoftDeletes;
	protected $table = 'organisations';
	public $timestamps = false;

	public function organisation_projets()
	{
		return $this->hasMany(OrganisationProjet::class, 'organisation_id');
	}
}
