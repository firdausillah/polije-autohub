<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ServiceSchedule extends Model
{
    use HasFactory, SoftDeletes;
    use LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
            'id',
            'vehicle_id',
            'kepala_unit_id',
            'name',
            'kode',
            'keterangan',
            'created_by',
            'updated_by',
            'deleted_at',
            'created_at',
            'updated_at',
            'approved_by',
            'approved_at',
            'is_approve',
            'mekanik_name',
            'registration_number',
            'vehicle_kode',
            'kepala_unit_name',
            'customer_name',
            'nomor_telepon',
            'keluhan',
            'km_datang',
            'service_status',
            'working_start',
            'working_end',
            'total_estimasi_waktu',
            'harga_subtotal',
            'discount',
            'pajak_total',
            'service_total',
            'sparepart_total',
            'total',
            'discount_service_total',
            'discount_sparepart_total',
            'discount_total',
            'payment_change',
            'invoice_file',
            'is_customer_umum',
            ])
            ->logOnlyDirty()
            ->useLogName('service_schedule');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "service_schedule telah di{$eventName}";
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::id();
            
            if(auth()->user()->hasRole('Kepala Unit')){
                $model->kepala_unit_id = Auth::id();
                $model->kepala_unit_name = Auth::user()->name;
                $model->service_status = 'Proses Pengerjaan';
                $model->working_start = NOW();
            }

        });

        static::updated(function ($model) {
            $model->updated_by = Auth::id();
        });

        static::created(function ($model){
            $checklists = Checklist::get();
            foreach ($checklists as $key => $value) {
                ServiceDChecklist::create([
                    'service_schedule_id' => $model->id,
                    'checklist_id' => $value->id,
                ]);
            }
        });
    }

    // Belongsto
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function serviceDMekanik(): HasMany
    {
        return $this->hasMany(ServiceDMekanik::class, 'service_schedule_id');
    }

    // public function mekanik1(): BelongsTo
    // {
    //     return $this->belongsTo(UserRole::class, 'mekanik1_id')->where('role_name', 'like', 'Mekanik%');
    // }

    // public function mekanik2(): BelongsTo
    // {
    //     return $this->belongsTo(UserRole::class, 'mekanik2_id')->where('role_name', 'like', 'Mekanik%');
    // }

    // public function mekanik3(): BelongsTo
    // {
    //     return $this->belongsTo(UserRole::class, 'mekanik3_id')->where('role_name', 'like', 'Mekanik%');
    // }

    public function kepalaMekanik(): BelongsTo
    {
        return $this->belongsTo(UserRole::class)->where('role_name', 'like', 'Kepala Unit%');
    }

    public function getChecklistStatusAttribute()
    {
        return [$this->serviceDChecklist->sum('checklist_hasil') == $this->serviceDChecklist->count()];
    }

    // HasMany
    public function serviceDChecklist(): HasMany
    {
        return $this->hasMany(ServiceDChecklist::class, 'service_schedule_id');
    }

    public function serviceDServices(): HasMany
    {
        return $this->hasMany(ServiceDServices::class);
    }

    public function serviceDSparepart(): HasMany
    {
        return $this->hasMany(ServiceDSparepart::class);
    }

    public function serviceDPayment(): HasMany
    {
        return $this->hasMany(ServiceDPayment::class);
    }
}
