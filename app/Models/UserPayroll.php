<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPayroll extends Model
{
    protected $table = 'user_payroll';

    public function payrollJurnals()
    {
        return $this->hasMany(PayrollJurnal::class, 'user_id');
    }

}
