<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

        static::saving(function ($model) {

            if ($model->signature_path && str_contains($model->signature_path, 'base64,')) {

                // ========= 1. Extract Base64 =========
                $data = explode('base64,', $model->signature_path)[1] ?? null;
                if (!$data) return;

                $binary = base64_decode($data);
                if (!$binary) return;

                // Buat image dari base64
                $image = imagecreatefromstring($binary);
                if (!$image) return;

                $width  = imagesx($image);
                $height = imagesy($image);

                // ========= 2. Cari bounding box berdasarkan pixel NON-TRANSPARAN =========
                $minX = $width;
                $minY = $height;
                $maxX = 0;
                $maxY = 0;

                for ($x = 0; $x < $width; $x++) {
                    for ($y = 0; $y < $height; $y++) {

                        $rgba = imagecolorat($image, $x, $y);
                        $alpha = ($rgba & 0x7F000000) >> 24; // 0 = opaque, 127 = full transparent

                        // Pixel dianggap "terpakai" jika tidak terlalu transparan
                        if ($alpha < 120) { // threshold aman untuk canvas signature
                            if ($x < $minX) $minX = $x;
                            if ($y < $minY) $minY = $y;
                            if ($x > $maxX) $maxX = $x;
                            if ($y > $maxY) $maxY = $y;
                        }
                    }
                }

                // Jika kosong (tidak ada coretan)
                if ($minX > $maxX || $minY > $maxY) {
                    return;
                }

                // ========= 3. Hitung ukuran crop =========
                $cropWidth  = $maxX - $minX + 1;
                $cropHeight = $maxY - $minY + 1;

                // ========= 4. Buat image baru transparan =========
                $croppedImage = imagecreatetruecolor($cropWidth, $cropHeight);
                imagesavealpha($croppedImage, true);
                $transparent = imagecolorallocatealpha($croppedImage, 0, 0, 0, 127);
                imagefill($croppedImage, 0, 0, $transparent);
 
                imagecopy(
                    $croppedImage,
                    $image,
                    0,
                    0,
                    $minX,
                    $minY,
                    $cropWidth,
                    $cropHeight
                );

                // ========= 5. Simpan PNG =========
                $fileName = 'signature-' . $model->id.'.png';
                // $fileName = 'signature-' . $model->id . '-' . time() . '.png';
                $path = storage_path('app/public/signatures/' . $fileName);

                imagepng($croppedImage, $path);

                // Simpan path di database
                $model->signature_path = 'signatures/' . $fileName;

                // Cleanup
                imagedestroy($image);
                imagedestroy($croppedImage);
            }
        });

    }

    public function deleteFile()
    {
        // dd($this);
        if ($this->path && Storage::exists($this->path)) {
            Storage::delete($this->path);
        }

        $this->path = null;
        $this->save();
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

    public function kepalaUnit(): BelongsTo
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
