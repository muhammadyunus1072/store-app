<?php

namespace App\Jobs\Rsmh\Sync;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Rsmh\Sync\syncPembelianRT;
use App\Models\Rsmh\Sync\SyncPengeluaranRt;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Logistic\Master\Unit\UnitDetail;
use App\Repositories\Rsmh\GudangLog\Suplier\SuplierRepository;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;
use App\Repositories\Logistic\Master\Warehouse\WarehouseRepository;
use App\Repositories\Rsmh\GudangLog\PembelianRT\PembelianRTRepository;
use App\Repositories\Rsmh\GudangLog\PengeluaranRT\PengeluaranRTRepository;
use App\Repositories\Logistic\Transaction\StockRequest\StockRequestRepository;
use App\Repositories\Purchasing\Transaction\PurchaseOrder\PurchaseOrderRepository;
use App\Repositories\Logistic\Transaction\StockRequest\StockRequestProductRepository;
use App\Repositories\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProductRepository;

class SyncPengeluaranRTJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    
    /**
     * Create a new job instance.
     */
    public function __construct(
        public $syncPengeluaranRTId,
        public $sourceWarehouseId,
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
            
            $dataPengeluaranRT = PengeluaranRTRepository::getSync($this->limit, $this->offset);
            foreach($dataPengeluaranRT as $key => $value)
            {
                $stockRequest = StockRequestRepository::findBy(whereClause:[
                    ['note', preg_replace('/\s+/', '', $value['note'])]
                ]);
                if(!$stockRequest){
                    $destinationWarehouse = WarehouseRepository::findBy(whereClause:[
                        ['id_sub', preg_replace('/\s+/', '', $value['id_sub'])]
                    ]);
                    if(!$destinationWarehouse)
                    {
                        Log::info("No Gudang : ".$value['id_sub']);
                    }
                    $validatedData = [
                        'source_company_id' => 1,
                        'destination_company_id' => 1,
                        'source_warehouse_id' => $this->sourceWarehouseId,
                        'destination_warehouse_id' => $destinationWarehouse->id,
                        'transaction_date' => $value['transaction_date'],
                        'note' => $value['note'],
                    ];

                    $stockRequest = StockRequestRepository::create($validatedData);
                    
                }

                $product = ProductRepository::findBy(whereClause:[
                    ['kode_simrs', preg_replace('/\s+/', '', $value['id_barang'])]
                ]);
                if($product)
                {
                    $validatedData = [
                        'stock_request_id' => $stockRequest->id,
                        'product_id' => $product->id,
                        'unit_detail_id' => $product->unit->unitDetailMain->id,
                        'quantity' => $value['quantity'],
                    ];
    
                    $object = StockRequestProductRepository::create($validatedData);
                }else{
                    Log::info("No Product : ".$value['id_barang']);
                }
                SyncPengeluaranRt::onJobSuccess($this->syncPengeluaranRTId);
            }


            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();

            $message = "ERROR SYNC Pengeluaran ID: {$this->syncPengeluaranRTId}) : " . $exception->getMessage();
            SyncPengeluaranRt::onJobFail($this->syncPengeluaranRTId, $message);
        }
    }

    public function failed(?\Throwable $exception): void
    {
        $message = "ERROR SYNC Pengeluaran ID: {$this->syncPengeluaranRTId}) : " . $exception->getMessage();
        SyncPengeluaranRt::onJobFail($this->syncPengeluaranRTId, $message);
    }
}
