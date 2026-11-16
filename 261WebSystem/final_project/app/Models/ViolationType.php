<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViolationType extends Model
{
    protected $table = 'violation_types';
    protected $primaryKey = 'violation_type_id';
    public $timestamps = false;
    protected $guarded = [];
}
