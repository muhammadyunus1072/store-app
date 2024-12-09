<?php

namespace App\Models\Rsmh\Sync;

use Illuminate\Support\Facades\Log;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use App\Jobs\Rsmh\Sync\SyncWarehouseJob;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SyncWarehouse extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'is_done',
        'is_error',
        'error_message',
        'total',
        'progress',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
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
            Log::info("Warehouse $offset to $jobLimit");
            SyncWarehouseJob::dispatch($this->id, $jobLimit, $offset);
        }
    }

    public function isDeletable()
    {
        return true;
    }

    public function isEditable()
    {
        return true;
    }
}
