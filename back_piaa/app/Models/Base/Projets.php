<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Evenements;
use App\Models\Fichiers;
use App\Models\OrganisationProjet;
use App\Models\ProjetAproposSections;
use App\Models\ProjetEnCours;
use App\Models\ProjetPersonnes;
use App\Models\Realisations;
use App\Models\ReseauxSociaux;
use App\Models\Temoignages;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Projets
 * 
 * @property int $id
 * @property string $nom
 * @property string|null $description
 * @property string|null $logo
 * @property Carbon|null $debut
 * @property int|null $duree_annee
 * @property string|null $deleted_at
 * @property string|null $devise
 * @property string|null $acronymes
 * @property string|null $adresse
 * @property string|null $email
 * @property string|null $telephone
 * @property bool|null $archived
 * 
 * @property Collection|Evenements[] $evenements
 * @property Collection|ProjetEnCours[] $projet_en_cours
 * @property Collection|OrganisationProjet[] $organisation_projets
 * @property Collection|Temoignages[] $temoignages
 * @property Collection|Fichiers[] $fichiers
 * @property Collection|ReseauxSociaux[] $reseaux_sociaux
 * @property Collection|ProjetPersonnes[] $projet_personnes
 * @property Collection|Realisations[] $realisations
 * @property Collection|ProjetAproposSections[] $projet_apropos_sections
 *
 * @package App\Models\Base
 */
class Projets extends Model
{
	use SoftDeletes;
	protected $table = 'projets';
	public $timestamps = false;

	protected $casts = [
		'debut' => 'datetime',
		'duree_annee' => 'int',
		'archived' => 'bool'
	];

	public function evenements()
	{
		return $this->hasMany(Evenements::class, 'projet_id');
	}

	public function projet_en_cours()
	{
		return $this->hasMany(ProjetEnCours::class, 'projet_id');
	}

	public function organisation_projets()
	{
		return $this->hasMany(OrganisationProjet::class, 'projet_id');
	}

	public function temoignages()
	{
		return $this->hasMany(Temoignages::class, 'projet_id');
	}

	public function fichiers()
	{
		return $this->hasMany(Fichiers::class, 'projet_id');
	}

	public function reseaux_sociaux()
	{
		return $this->belongsToMany(ReseauxSociaux::class, 'projets_reseaux_sociaux', 'projet_id', 'reseau_social_id')
					->withPivot('id', 'url', 'deleted_at');
	}

	public function projet_personnes()
	{
		return $this->hasMany(ProjetPersonnes::class, 'projet_id');
	}

	public function realisations()
	{
		return $this->hasMany(Realisations::class, 'projet_id');
	}

	public function projet_apropos_sections()
	{
		return $this->hasMany(ProjetAproposSections::class, 'projet_id');
	}
}
