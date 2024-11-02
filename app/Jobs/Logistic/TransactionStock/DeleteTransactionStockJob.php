<?php

namespace App\Jobs\Logistic\TransactionStock;

use App\Models\Logistic\Transaction\TransactionStock\TransactionStock;
use App\Repositories\Logistic\Transaction\TransactionStock\TransactionStockRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class DeleteTransactionStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public $remarksId,
        public $remarksType
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            DB::beginTransaction();

            $transaction = TransactionStockRepository::findBy(
                whereClause: [
                    ['remarks_id', $this->remarksId],
                    ['remarks_type', $this->remarksType],
                ],
                lockForUpdate: true,
            );
            $transaction->status = TransactionStock::STATUS_DELETE;
            $transaction->save();

            DB::commit();
            
            // Process Transaction
            ProcessTransactionStockJob::dispatch();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("ERROR DELETE TRANSACTION STOCK JOB ({$this->remarksType} | ID: {$this->remarksId}) : " . $e->getMessage());
        }
    }

    public function failed(?Throwable $e): void
    {
        Log::error("FAILED DELETE TRANSACTION STOCK JOB ({$this->remarksType} | ID: {$this->remarksId}) : " . $e->getMessage());
    }
}
