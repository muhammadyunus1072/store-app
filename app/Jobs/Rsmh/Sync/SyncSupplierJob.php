<?php

namespace App\Jobs\Rsmh\Sync;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Rsmh\Sync\SyncSupplier;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Repositories\Rsmh\GudangLog\Suplier\SuplierRepository;
use App\Repositories\Purchasing\Master\Supplier\SupplierRepository;

class SyncSupplierJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     */
    public function __construct(
        public $syncSupplierId,
        public $limit,
        public $offset,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            DB::beginTransaction();

            $data = SuplierRepository::getSync($this->limit, $this->offset);
            foreach ($data as $item) {
                SupplierRepository::createOrUpdate([
                    'name' => $item->name,
                    'kode_simrs' => $item->kode_simrs,
                ]);
                SyncSupplier::onJobSuccess($this->syncSupplierId);
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            $message = "ERROR SYNC SUPPLIER ID: {$this->syncSupplierId}) : " . $exception->getMessage();
            SyncSupplier::onJobFail($this->syncSupplierId, $message);
        }
    }

    public function failed(?\Throwable $exception): void
    {
        $message = "ERROR SYNC SUPPLIER ID: {$this->syncSupplierId}) : " . $exception->getMessage();
        SyncSupplier::onJobFail($this->syncSupplierId, $message);
    }
}
