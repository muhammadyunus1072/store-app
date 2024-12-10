<?php

namespace App\Jobs\Rsmh\Sync;

use App\Helpers\General\ImportDataHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Rsmh\Sync\syncPembelianRT;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;
use App\Repositories\Purchasing\Master\Supplier\SupplierRepository;
use App\Repositories\Rsmh\GudangLog\PembelianRT\PembelianRTRepository;
use App\Repositories\Purchasing\Transaction\PurchaseOrder\PurchaseOrderRepository;
use App\Repositories\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProductRepository;

class SyncPembelianRtJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     */
    public function __construct(
        public $syncPembelianRTId,
        public $warehouseId,
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

            $dataSupplier = PembelianRTRepository::getSync($this->limit, $this->offset);
            foreach ($dataSupplier as $key => $value) {
                $supplier = SupplierRepository::findBy(whereClause: [
                    ['kode_simrs', $value['kode_simrs']]
                ]);
                if (!$supplier) {
                    Log::info("No Supplier " . $value['kode_simrs']);
                    continue;
                }

                $obj = PurchaseOrderRepository::findBy(whereClause: [
                    ['no_spk', $value['no_spk']]
                ]);
                if (!$obj) {
                    $validatedData = [
                        'company_id' => 1,
                        'supplier_id' => $supplier->id,
                        'no_spk' => $value['no_spk'],
                        'warehouse_id' => $this->warehouseId,
                        'transaction_date' => $value['transaction_date'],
                        'note' => 'Sync Data',
                        'supplier_invoice_number' => null,
                    ];
                    $obj = PurchaseOrderRepository::create($validatedData);
                }
                
                $product = ProductRepository::findBy(whereClause: [
                    ['kode_simrs', $value['id_barang']]
                ]);
                $unit_detail = UnitDetailRepository::findBy(whereClause: [
                    ['name', isset(ImportDataHelper::TRANSLATE_UNIT[strtoupper(strtoupper($value['unit_name']))]) ? ImportDataHelper::TRANSLATE_UNIT[strtoupper(strtoupper($value['unit_name']))] : strtoupper(strtoupper($value['unit_name']))]
                ]);
                if (!$product) {
                    Log::info("No Product " . $value['id_barang']);
                    continue;
                }
                if (!$unit_detail) {
                    Log::info("No UNIT " . $value['unit_name']);
                    continue;
                }

                $validatedData = [
                    'purchase_order_id' => $obj->id,
                    'product_id' => $product->id,
                    'unit_detail_id' => $unit_detail->id,
                    'quantity' => $value['quantity'],
                    'price' => $value['price'] * 100 / 11,
                    'code' => null,
                    'batch' => null,
                    'expired_date' => null
                ];
                $object = PurchaseOrderProductRepository::create($validatedData);

                syncPembelianRT::onJobSuccess($this->syncPembelianRTId);
            }


            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();

            $message = "ERROR SYNC Pembelian ID: {$this->syncPembelianRTId}) : " . $exception->getMessage();
            syncPembelianRT::onJobFail($this->syncPembelianRTId, $message);
        }
    }

    public function failed(?\Throwable $exception): void
    {
        $message = "ERROR SYNC Pembelian ID: {$this->syncPembelianRTId}) : " . $exception->getMessage();
        syncPembelianRT::onJobFail($this->syncPembelianRTId, $message);
    }
}
