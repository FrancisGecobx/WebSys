<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PodSlipHistory extends Model
{
    protected $table = 'pod_slip_history';
    protected $primaryKey = 'history_id';
    public $timestamps = false;
    protected $guarded = [];
}
