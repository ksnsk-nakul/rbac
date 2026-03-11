<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'payload',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'applied_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'approved_at' => 'datetime',
        'applied_at' => 'datetime',
    ];
}
