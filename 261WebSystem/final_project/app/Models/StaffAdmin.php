<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAdmin extends Model
{
    protected $table = 'staff_admin';
    protected $primaryKey = 'staff_admin_id';
    public $timestamps = false;
    protected $guarded = [];
}