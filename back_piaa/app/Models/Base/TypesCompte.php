<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TypesCompte
 * 
 * @property int $id
 * @property string $type
 * @property int $niveau_acces
 * @property string|null $deleted_at
 * 
 * @property Collection|Users[] $users
 *
 * @package App\Models\Base
 */
class TypesCompte extends Model
{
	use SoftDeletes;
	protected $table = 'types_compte';
	public $timestamps = false;

	protected $casts = [
		'niveau_acces' => 'int'
	];

	public function users()
	{
		return $this->hasMany(User::class, 'type_compte_id');
	}
}
