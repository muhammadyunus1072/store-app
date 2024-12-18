<?php

namespace App\Models\Rsmh\Sakti;

use Illuminate\Support\Facades\Log;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Jobs\Rsmh\Sakti\GenerateInterkoneksiSaktiDetailBarangJob;
use App\Repositories\Rsmh\Sakti\GenerateInterkoneksiSaktiDetailBarang\GenerateInterkoneksiSaktiDetailBarangRepository;

class GenerateInterkoneksiSaktiDetailBarang extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'is_done',
        'is_error',
        'error_message',
        'total',
        'progress',
        'warehouse_id',
        'date_start',
        'date_end',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model->total = GenerateInterkoneksiSaktiDetailBarangRepository::getData($model->warehouse_id, $model->date_start, $model->date_end)->count();
        });
        self::created(function ($model) {
            $model->dispatchJob();
        });

        self::updating(function ($model) {
            if ($model->total <= $model->progress) {
                $model->is_done = true;
            }
        });
    }

    public static function onJobSuccess($id)
    {
        $obj = self::lockForUpdate()->find($id);
        $obj->progress++;
        $obj->save();
    }

    public static function onJobFail($id, $message)
    {
        $obj = self::lockForUpdate()->find($id);
        $obj->progress++;
        $obj->is_error = true;
        $obj->error_message = $message;
        $obj->save();
    }

    public function dispatchJob()
    {
        $limit = 100;
        for ($offset = 0; $offset < $this->total; $offset += $limit) {
            $jobLimit = min($limit, $this->total - $offset);
            Log::debug("G ".$this->id. $this->warehouse_id. $this->date_start. $this->date_end. $jobLimit. $offset);
            GenerateInterkoneksiSaktiDetailBarangJob::dispatch($this->id, $this->warehouse_id, $this->date_start, $this->date_end, $jobLimit, $offset);
        }
    }
}
