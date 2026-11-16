<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PodSlip extends Model
{
    protected $table = 'pod_slip';
    protected $primaryKey = 'pod_slip_id';
    public $timestamps = false;
    protected $guarded = [];

    // Relationship to Student
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    // Relationship to Disciplinary Actions
    public function disciplinaryActions()
    {
        return $this->hasMany(DisciplinaryAction::class, 'pod_slip_id', 'pod_slip_id');
    }
}