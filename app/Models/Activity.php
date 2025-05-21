<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $table = 'activity_log';


    public function user():BelongsTo{
        return $this->belongsTo(User::class, 'causer_id');
    }
}
