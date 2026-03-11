<?php

namespace Modules\Reader\Models;

use Illuminate\Database\Eloquent\Model;

class ReaderItem extends Model
{
    protected $fillable = [
        'title',
        'status',
    ];
}
