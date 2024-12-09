<?php

namespace App\Jobs\Rsmh\Sync;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use App\Models\Rsmh\Sync\SyncWarehouse;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Repositories\Rsmh\GudangLog\SubBagian\SubBagianRepository;
use App\Repositories\Logistic\Master\Warehouse\WarehouseRepository;

class SyncWarehouseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    
    /**
     * Create a new job instance.
     */
    public function __construct(
        public $syncWarehouseId,
        public $limit,
        public $offset,
    )
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            DB::beginTransaction();
            
            $dataSubBagian = SubBagianRepository::getSync($this->limit, $this->offset);
            foreach($dataSubBagian as $key => $value)
            {
                $validatedData = [
                    'name' => $value->name,
                    'id_sub' => $value->id_sub,
                    'id_bagian' => $value->id_bagian,
                    'id_direktorat' => $value->id_direktorat,
                ];
                $obj = WarehouseRepository::createOrUpdate($validatedData);
                SyncWarehouse::onJobSuccess($this->syncWarehouseId);
            }


            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();

            $message = "ERROR SYNC WAREHOUSE ID: {$this->syncWarehouseId}) : " . $exception->getMessage();
            SyncWarehouse::onJobFail($this->syncWarehouseId, $message);
        }
    }

    public function failed(?\Throwable $exception): void
    {
        $message = "ERROR SYNC WAREHOUSE ID: {$this->syncWarehouseId}) : " . $exception->getMessage();
        SyncWarehouse::onJobFail($this->syncWarehouseId, $message);
    }
}
