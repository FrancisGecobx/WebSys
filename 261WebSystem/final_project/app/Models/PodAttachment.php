<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PodAttachment extends Model
{
    protected $table = 'pod_attachment';
    protected $primaryKey = 'attachment_id';
    public $timestamps = false;
    protected $guarded = [];
}
