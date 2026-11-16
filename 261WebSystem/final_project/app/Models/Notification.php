<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'notification_id';
    public $timestamps = false; // Using custom created_at datetime column

    protected $fillable = [
        'pod_slip_id',
        'student_id',
        'staff_admin_id',
        'message',
        'is_read',
        'delivery_status',
        'created_at',
    ];

    public function podSlip()
    {
        return $this->belongsTo(PodSlip::class, 'pod_slip_id', 'pod_slip_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function staffAdmin()
    {
        return $this->belongsTo(StaffAdmin::class, 'staff_admin_id', 'staff_admin_id');
    }
}