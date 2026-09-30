<?php

namespace App\Models;

use App\Models\Base\Fichiers as BaseFichiers;
use Illuminate\Support\Facades\Storage;
use OwenIt\Auditing\Contracts\Auditable;

class Fichiers extends BaseFichiers implements Auditable
{
	const ENTITE_TYPE_PROJET = 1;
    const ENTITE_TYPE_EVENEMENT = 2;
    const ENTITE_TYPE_REALISATION = 3;
	const ENTITE_TYPE_PLAN_REPERAGE = 4;
	const ENTITE_TYPE_INTERVIEW = 5;
    const ENTITE_TYPE_ACTION_PROXIMITE = 6;
	
    use \OwenIt\Auditing\Auditable;
    
	protected $fillable = [
		'titre',
		'description',
		'type_fichier',
		'chemin',
		'date_upload',
		'entite_type_id',
		'entite_id',
		'niveau_acces',
		'projet_id',
		'archived'
	];

    /**
     * Retourne l'icône appropriée pour le type de fichier
     */
    public static function getFileIconStatic($fileType)
    {
        $icons = [
            'pdf' => 'fas fa-file-pdf',
            'doc' => 'fas fa-file-word',
            'docx' => 'fas fa-file-word',
            'xls' => 'fas fa-file-excel',
            'xlsx' => 'fas fa-file-excel',
            'jpg' => 'fas fa-file-image',
            'jpeg' => 'fas fa-file-image',
            'png' => 'fas fa-file-image',
            'gif' => 'fas fa-file-image',
            'svg' => 'fas fa-file-image',
            'webp' => 'fas fa-file-image',
            'bmp' => 'fas fa-file-image',
            'zip' => 'fas fa-file-archive',
            'rar' => 'fas fa-file-archive',
            '7z' => 'fas fa-file-archive',
            'tar' => 'fas fa-file-archive',
            'gz' => 'fas fa-file-archive',
            'txt' => 'fas fa-file-alt',
            'csv' => 'fas fa-file-csv',
            'mp3' => 'fas fa-file-audio',
            'wav' => 'fas fa-file-audio',
            'ogg' => 'fas fa-file-audio',
            'flac' => 'fas fa-file-audio',
            'aac' => 'fas fa-file-audio',
            'mp4' => 'fas fa-file-video',
            'avi' => 'fas fa-file-video',
            'mov' => 'fas fa-file-video',
            'wmv' => 'fas fa-file-video',
            'flv' => 'fas fa-file-video',
            'mkv' => 'fas fa-file-video',
            'webm' => 'fas fa-file-video',
            'm4v' => 'fas fa-file-video',
        ];

        return $icons[strtolower($fileType)] ?? 'fas fa-file';
    }

    /**
     * Retourne la classe CSS pour l'icône du fichier
     */
    public static function getFileIconClassStatic($fileType)
    {
        $types = [
            'pdf' => 'pdf',
            'doc' => 'doc', 'docx' => 'doc',
            'xls' => 'xls', 'xlsx' => 'xls',
            'jpg' => 'img', 'jpeg' => 'img', 'png' => 'img', 'gif' => 'img',
            'svg' => 'img', 'webp' => 'img', 'bmp' => 'img',
            'zip' => 'zip', 'rar' => 'zip', '7z' => 'zip', 'tar' => 'zip', 'gz' => 'zip',
            'txt' => 'txt', 'csv' => 'csv',
            'mp3' => 'audio', 'wav' => 'audio', 'ogg' => 'audio', 'flac' => 'audio', 'aac' => 'audio',
            'mp4' => 'video', 'avi' => 'video', 'mov' => 'video', 'wmv' => 'video',
            'flv' => 'video', 'mkv' => 'video', 'webm' => 'video', 'm4v' => 'video',
        ];

        return $types[strtolower($fileType)] ?? 'default';
    }

    /**
     * Retourne la taille formatée du fichier
     */
    public static function getFileSizeStatic($filePath)
    {
        if (!$filePath) {
            return 'Inconnue';
        }
        
        $cleanPath = str_replace('/storage/', '', $filePath);
        
        if (!Storage::disk('public')->exists($cleanPath)) {
            return 'Inconnue';
        }

        $size = Storage::disk('public')->size($cleanPath);
        
        if ($size >= 1073741824) {
            return number_format($size / 1073741824, 2) . ' GB';
        } elseif ($size >= 1048576) {
            return number_format($size / 1048576, 2) . ' MB';
        } elseif ($size >= 1024) {
            return number_format($size / 1024, 2) . ' KB';
        } else {
            return $size . ' bytes';
        }
    }
}
