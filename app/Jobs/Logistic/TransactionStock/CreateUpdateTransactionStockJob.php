<?php

namespace App\Jobs\Logistic\TransactionStock;

use App\Models\Logistic\Transaction\TransactionStock\TransactionStock;
use App\Repositories\Logistic\Transaction\TransactionStock\TransactionStockProductRepository;
use App\Repositories\Logistic\Transaction\TransactionStock\TransactionStockRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateUpdateTransactionStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public $remarksId,
        public $remarksType,
        public $transactionDate,
        public $transactionType,
        public $sourceCompanyId,
        public $sourceWarehouseId,
        public $products,
        public $destinationCompanyId,
        public $destinationLocationId,
        public $destinationLocationType,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        logger('Job sedang berjalan secara sync');

        try {
            DB::beginTransaction();

            $transaction = TransactionStockRepository::findBy(
                whereClause: [
                    ['remarks_id', $this->remarksId],
                    ['remarks_type', $this->remarksType]
                ],
                lockForUpdate: true
            );

            if (empty($transaction)) {
                // Create 

                $transaction = TransactionStockRepository::create([
                    'status' => TransactionStock::STATUS_NOT_PROCESSED,
                    'transaction_type' => $this->transactionType,
                    'transaction_date' => $this->transactionDate,
                    "source_company_id" => $this->sourceCompanyId,
                    "source_warehouse_id" => $this->sourceWarehouseId,
                    "destination_company_id" => $this->destinationCompanyId,
                    "destination_location_id" => $this->destinationLocationId,
                    "destination_location_type" => $this->destinationLocationType,
                    'remarks_id' => $this->remarksId,
                    'remarks_type' => $this->remarksType,
                ]);
                logger("create TRANSACTION job");
                logger($transaction);
                foreach ($this->products as $item) {
                    TransactionStockProductRepository::create([
                        'transaction_stock_id' => $transaction->id,
                        'product_id' => $item['product_id'],
                        'unit_detail_id' => $item['unit_detail_id'],
                        'quantity' => $item['quantity'],
                        'price' => isset($item['price']) ? $item['price'] : null,
                        'code' => isset($item['code']) ? $item['code'] : null,
                        'batch' => isset($item['batch']) ? $item['batch'] : null,
                        'expired_date' => isset($item['expired_date']) ? $item['expired_date'] : null,
                        'remarks_id' => $item['remarks_id'],
                        'remarks_type' => $item['remarks_type']
                    ]);
                }
            } else {
                // Update
                $transaction->fill([
                    'status' => TransactionStock::STATUS_REPROCESSED,
                    'transaction_type' => $this->transactionType,
                    'transaction_date' => $this->transactionDate,
                    "source_company_id" => $this->sourceCompanyId,
                    "source_warehouse_id" => $this->sourceWarehouseId,
                    "destination_company_id" => $this->destinationCompanyId,
                    "destination_location_id" => $this->destinationLocationId,
                    "destination_location_type" => $this->destinationLocationType,
                ]);
                $transaction->save();

                $affectedProductIds = [];
                foreach ($this->products as $item) {
                    $product = TransactionStockProductRepository::findBy(
                        whereClause: [
                            ['remarks_id', $item['remarks_id']],
                            ['remarks_type', $item['remarks_type']]
                        ],
                        lockForUpdate: true
                    );

                    if (empty($product)) {
                        $product = TransactionStockProductRepository::create([
                            'transaction_stock_id' => $transaction->id,
                            'product_id' => $item['product_id'],
                            'unit_detail_id' => $item['unit_detail_id'],
                            'quantity' => $item['quantity'],
                            'price' => isset($item['price']) ? $item['price'] : null,
                            'code' => isset($item['code']) ? $item['code'] : null,
                            'batch' => isset($item['batch']) ? $item['batch'] : null,
                            'expired_date' => isset($item['expired_date']) ? $item['expired_date'] : null,
                            'remarks_id' => $item['remarks_id'],
                            'remarks_type' => $item['remarks_type']
                        ]);
                    } else {
                        $product->fill([
                            'product_id' => $item['product_id'],
                            'unit_detail_id' => $item['unit_detail_id'],
                            'quantity' => $item['quantity'],
                            'price' => isset($item['price']) ? $item['price'] : null,
                            'code' => isset($item['code']) ? $item['code'] : null,
                            'batch' => isset($item['batch']) ? $item['batch'] : null,
                            'expired_date' => isset($item['expired_date']) ? $item['expired_date'] : null,
                        ]);
                        $product->save();
                    }

                    $affectedProductIds[] = $product->id;
                }

                // Delete Not Affected Products
                TransactionStockProductRepository::deleteBy(whereClause: [
                    ['transaction_stock_id', $transaction->id],
                    ['id', 'NOT IN', $affectedProductIds]
                ]);
            }

            DB::commit();

            // Process Transaction
            ProcessTransactionStockJob::dispatchSync();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("ERROR CREATE OR UPDATE TRANSACTION STOCK JOB ({$this->remarksType} | ID: {$this->remarksId}) : " . $e->getMessage());
        }
    }

    public function failed(?Throwable $e): void
    {
        Log::error("FAILED CREATE UPDATE TRANSACTION JOB ({$this->remarksType} | ID: {$this->remarksId}) : " . $e->getMessage());
    }
}
