<?php

namespace App\Jobs\Rsmh\Sakti;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Settings\InterkoneksiSaktiSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Rsmh\Sakti\InterkoneksiSaktiDetailCoa;
use App\Repositories\Rsmh\GudangLog\Suplier\SuplierRepository;
use App\Models\Rsmh\Sakti\GenerateInterkoneksiSaktiDetailCoa;
use App\Repositories\Purchasing\Master\Supplier\SupplierRepository;
use App\Repositories\Rsmh\Sakti\InterkoneksiSaktiCoa\InterkoneksiSaktiCoaRepository;
use App\Repositories\Rsmh\Sakti\InterkoneksiSaktiKbki\InterkoneksiSaktiKbkiRepository;
use App\Repositories\Rsmh\Sakti\InterkoneksiSaktiDetailCoa\InterkoneksiSaktiDetailCoaRepository;
use App\Repositories\Rsmh\Sakti\GenerateInterkoneksiSaktiDetailCoa\GenerateInterkoneksiSaktiDetailCoaRepository;

class GenerateInterkoneksiSaktiDetailCoaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     */
    public function __construct(
        public $generateInterkoneksiSaktiDetailCoaId,
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

            $data = GenerateInterkoneksiSaktiDetailCoaRepository::getData($this->limit, $this->offset);

            $interkoneksi_sakti_coa_vol_sub_output = InterkoneksiSaktiSetting::get(InterkoneksiSaktiSetting::COA_VOL_SUB_OUTPUT);

            foreach ($data as $item) {
                $lastKodeUpload = InterkoneksiSaktiDetailCoaRepository::findLastKodeUpload();
                $lastKode = strval(($lastKodeUpload ? substr($lastKodeUpload->kode_upload, -4) : "0000") + 1);
                $kode = str_pad($lastKode, 4, "0", STR_PAD_LEFT);
                $kodeUpload = Carbon::now()->format('Ym').$kode;

                $data = [
                    'kode_upload' => $kodeUpload,
                    'no_dokumen' => $item->no_dokumen,
                    'kode_coa' => $item->kode_coa,
                    'nilai_coa_detail' => $item->nilai,
                    'nilai_valas_detail' => $item->nilai,
                    'vol_sub_output' => $interkoneksi_sakti_coa_vol_sub_output,
                ];
                InterkoneksiSaktiDetailCoa::create($data);
                GenerateInterkoneksiSaktiDetailCoa::onJobSuccess($this->generateInterkoneksiSaktiDetailCoaId);
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            $message = "ERROR GENERATE INTERKONEKSI SAKTI DETAIL COA ID: {$this->generateInterkoneksiSaktiDetailCoaId}) : " . $exception->getMessage();
            GenerateInterkoneksiSaktiDetailCoa::onJobFail($this->generateInterkoneksiSaktiDetailCoaId, $message);
        }
    }

    public function failed(?\Throwable $exception): void
    {
        $message = "ERROR GENERATE INTERKONEKSI SAKTI DETAIL COA ID: {$this->generateInterkoneksiSaktiDetailCoaId}) : " . $exception->getMessage();
        GenerateInterkoneksiSaktiDetailCoa::onJobFail($this->generateInterkoneksiSaktiDetailCoaId, $message);
    }
}
