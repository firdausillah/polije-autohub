<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalaryBonus extends Model
{
    protected $guarded;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::id();
        });

        static::created(function ($model) {

            $start_date = $model->start_date;
            $end_date = Carbon::parse($model->end_date)->addDay()->toDateString();
            DB::insert(
                "
                INSERT INTO salary_d_bonuses (
                    salary_bonus_id,
                    user_id, role_id, payroll_id,
                    created_by, created_at,
                    sumber_pendapatan, pendapatan, salary, bonus
                )
                SELECT
                    ?,
                    data.user_id,
                    data.role_id,
                    data.payroll_id,
                    ?, NOW(),
                    data.sumber_pendapatan,
                    data.pendapatan,
                    CASE
                        WHEN data.payroll_id = 6 THEN data.pendapatan * 0.05
                        WHEN data.min_gaji = 0 THEN 0
                        WHEN data.pendapatan > data.min_gaji THEN data.gaji_pokok
                        ELSE data.pendapatan * 0.5
                    END AS salary,
                    CASE
                        WHEN data.pendapatan < data.min_bonus OR data.payroll_id = 6 THEN 0
                        ELSE data.pendapatan * (data.persentase_bonus / 100)
                    END AS bonus
                FROM (
                    SELECT
                        u.user_id,
                        u.role_id,
                        u.payroll_id,
                        u.sumber_pendapatan,
                        u.gaji_pokok,
                        u.min_gaji,
                        u.min_bonus,
                        u.persentase_bonus,
                        (
                            CASE 
                                WHEN u.role_id IN (1, 11) THEN -- Admin/Admin Toko
                                    COALESCE((
                                        SELECT SUM(sparepart_total)
                                        FROM service_schedules
                                        WHERE created_by = u.user_id
                                        AND is_approve = 'approved'
                                        AND approved_at BETWEEN ? AND ?
                                    ), 0)
                                    +
                                    COALESCE((
                                        SELECT SUM(total)
                                        FROM sparepart_sales
                                        WHERE created_by = u.user_id
                                        AND is_approve = 'approved'
                                        AND approved_at BETWEEN ? AND ?
                                    ), 0)

                                WHEN u.role_id = 9 THEN -- Mekanik
                                    COALESCE((
                                        SELECT SUM(service_total)
                                        FROM service_schedules
                                        WHERE mekanik_id = u.user_id
                                        AND is_approve = 'approved'
                                        AND approved_at BETWEEN ? AND ?
                                    ), 0)

                                WHEN u.role_id = 3 THEN -- Kepala Mekanik
                                    COALESCE((
                                        SELECT SUM(service_total)
                                        FROM service_schedules
                                        WHERE kepala_mekanik_id = u.user_id
                                        AND is_approve = 'approved'
                                        AND approved_at BETWEEN ? AND ?
                                    ), 0)

                                WHEN u.role_id = 8 THEN -- Manager
                                    COALESCE((
                                        SELECT SUM(sparepart_total)
                                        FROM service_schedules
                                        WHERE is_approve = 'approved'
                                        AND approved_at BETWEEN ? AND ?
                                    ), 0)
                                    +
                                    COALESCE((
                                        SELECT SUM(total)
                                        FROM sparepart_sales
                                        WHERE is_approve = 'approved'
                                        AND approved_at BETWEEN ? AND ?
                                    ), 0)

                                ELSE 0
                            END
                        ) AS pendapatan
                    FROM (
                        SELECT
                            a.id AS user_id,
                            b.role_id,
                            b.id AS payroll_id,
                            b.sumber_pendapatan,
                            b.gaji_pokok,
                            b.minimal_pendapatan_untuk_mendapat_gaji_pokok AS min_gaji,
                            b.minimal_pendapatan_untuk_mendapat_bonus AS min_bonus,
                            b.persentase_bonus
                        FROM users a
                        LEFT JOIN payrolls b ON a.payroll_id = b.id
                    ) u
                ) AS data
                ",
                [
                    $model->id,
                    Auth::id(),       // created_by
                    $start_date, $end_date, // admin - service_schedules
                    $start_date, $end_date, // admin - sparepart_sales
                    $start_date, $end_date, // mekanik
                    $start_date, $end_date, // kepala mekanik
                    $start_date, $end_date, // manager - service_schedules
                    $start_date, $end_date, // manager - sparepart_sales
                ]
            );

            $totals = SalaryDBonus::where('salary_bonus_id', $model->id)
                ->selectRaw('SUM(salary) as salary_total, SUM(bonus) as bonus_total, SUM(pendapatan) as pendapatan_total')
                ->first();
                
            $model->salary_total = $totals->salary_total ?? 0;
            $model->bonus_total = $totals->bonus_total ?? 0;
            $model->pendapatan_total = $totals->pendapatan_total ?? 0;

            $model->save();




        });

        static::deleting(function ($model) {
            $model->salaryDBonus()->delete();
        });
    }

    public function salaryDBonus(): HasMany
    {
        return $this->hasMany(SalaryDBonus::class, 'salary_bonus_id');
    }
}
