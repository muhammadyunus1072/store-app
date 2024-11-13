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
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Throwable;

class ProcessTransactionStockJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $uniqueFor = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct() {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            DB::beginTransaction();

            $oldestTransaction = TransactionStockRepository::findOldestNeedToBeProcessed();

            if ($oldestTransaction) {
                // Cancel Oldest Transaction
                if ($oldestTransaction->status == TransactionStock::STATUS_REPROCESSED) {
                    $oldestTransaction->cancel();
                } else if ($oldestTransaction->status == TransactionStock::STATUS_DELETE) {
                    $oldestTransaction->cancel();
                    $oldestTransaction->delete();
                }

                // Cancel Newer Transactions
                $transactions = TransactionStockRepository::getNewerTransactions($oldestTransaction);
                foreach ($transactions as $newerTransaction) {
                    $newerTransaction->cancel();
                }

                // Process All Not Processed Transaction
                $transactions = TransactionStockRepository::getNotProcessedTransactions();
                foreach ($transactions as $transaction) {
                    $transaction->process();
                }
            }

            DB::commit();

            // Recheck Transaction
            if ($oldestTransaction) {
                self::dispatch();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("ERROR PROCESS TRANSACTION JOB : " . $e->getMessage());

            $oldestTransaction->status_message = $e->getMessage();
            $oldestTransaction->save();
        }
    }

    public function failed(?Throwable $e): void
    {
        Log::error("FAILED PROCESS TRANSACTION JOB : " . $e->getMessage());
    }

    public function uniqueId(): string
    {
        return self::class;
    }
}
