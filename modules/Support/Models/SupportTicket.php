<?php

namespace Modules\Support\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = [
        'subject',
        'status',
        'priority',
    ];
}
