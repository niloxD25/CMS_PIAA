<?php

namespace App\Models;

use App\Models\Base\Messages as BaseMessages;
use OwenIt\Auditing\Contracts\Auditable;

class Messages extends BaseMessages implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	
	protected $fillable = [
        'nom',
        'mail',
        'telephone',
        'message',
        'type_message_id',
        'is_read',
        'read_at'
    ];
}
