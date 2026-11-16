<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisciplinaryAction extends Model
{
    protected $table = 'disciplinary_actions';
    protected $primaryKey = 'action_id';
    public $timestamps = false;
    protected $guarded = [];

    // Relationship to POD Slip
    public function podSlip()
    {
        return $this->belongsTo(PodSlip::class, 'pod_slip_id', 'pod_slip_id');
    }

    // Relationship to Staff/Admin
    public function staffAdmin()
    {
        return $this->belongsTo(StaffAdmin::class, 'staff_admin_id', 'staff_admin_id');
    }
}