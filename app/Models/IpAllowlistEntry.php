<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpAllowlistEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_id',
        'ip_address',
        'label',
    ];
}
