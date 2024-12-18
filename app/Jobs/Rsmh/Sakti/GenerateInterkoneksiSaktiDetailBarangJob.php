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
use App\Models\Rsmh\Sakti\InterkoneksiSaktiDetailBarang;
use App\Repositories\Rsmh\GudangLog\Suplier\SuplierRepository;
use App\Models\Rsmh\Sakti\GenerateInterkoneksiSaktiDetailBarang;
use App\Repositories\Purchasing\Master\Supplier\SupplierRepository;
use App\Repositories\Rsmh\Sakti\InterkoneksiSaktiDetailBarang\InterkoneksiSaktiDetailBarangRepository;
use App\Repositories\Rsmh\Sakti\GenerateInterkoneksiSaktiDetailBarang\GenerateInterkoneksiSaktiDetailBarangRepository;
use App\Repositories\Rsmh\Sakti\InterkoneksiSaktiCoa\InterkoneksiSaktiCoaRepository;
use App\Repositories\Rsmh\Sakti\InterkoneksiSaktiKbki\InterkoneksiSaktiKbkiRepository;

class GenerateInterkoneksiSaktiDetailBarangJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     */
    public function __construct(
        public $generateInterkoneksiSaktiDetailBarangId,
        public $warehouseId,
        public $dateStart,
        public $dateEnd,
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

            $data = GenerateInterkoneksiSaktiDetailBarangRepository::getData($this->warehouseId, $this->dateStart, $this->dateEnd, $this->limit, $this->offset);

            $kode_uakpb = InterkoneksiSaktiSetting::get(InterkoneksiSaktiSetting::BARANG_KODE_UAKPB);
            $interkoneksi_sakti_persentase_tkdn = InterkoneksiSaktiSetting::get(InterkoneksiSaktiSetting::BARANG_PERSENTASE_TKDN);
            $interkoneksi_sakti_kategori_pdn = InterkoneksiSaktiSetting::get(InterkoneksiSaktiSetting::BARANG_KATEGORI_PDN);
            $interkoneksi_sakti_kbki_id = InterkoneksiSaktiSetting::get(InterkoneksiSaktiSetting::BARANG_INTERKONEKSI_SAKTI_KBKI_ID);
            $kode_kbki = InterkoneksiSaktiKbkiRepository::findBy(whereClause:[
                ['id', $interkoneksi_sakti_kbki_id]
            ])->nama;
            $interkoneksi_sakti_coa_id = InterkoneksiSaktiSetting::get(InterkoneksiSaktiSetting::HEADER_INTERKONEKSI_SAKTI_COA_12_ID);
            $kode_coa = InterkoneksiSaktiCoaRepository::findBy(whereClause:[
                ['id', $interkoneksi_sakti_coa_id]
            ])->kode;
            foreach ($data as $item) {
                $lastKodeUpload = InterkoneksiSaktiDetailBarangRepository::findLastKodeUpload();
                $lastKode = strval(($lastKodeUpload ? substr($lastKodeUpload->kode_upload, -4) : "0000") + 1);
                $kode = str_pad($lastKode, 4, "0", STR_PAD_LEFT);
                $kodeUpload = Carbon::now()->format('Ym').$kode;

                $data = [
                    'kode_upload' => $kodeUpload,
                    'kode_barang' => $item->kode_sakti,
                    'jumlah_barang' => abs($item->quantity),
                    'harga_satuan' => $item->price,
                    'kode_uakpb' => $kode_uakpb,
                    'kode_kbki' => $item->kode_kbki ? $item->kode_kbki : ($kode_kbki ? $kode_kbki : null),
                    'kode_coa' => $item->kode_coa ? $item->kode_coa : null,
                    'persentase_tkdn' => $item->interkoneksi_sakti_persentase_tkdn ? $item->interkoneksi_sakti_persentase_tkdn : ($interkoneksi_sakti_persentase_tkdn ? $interkoneksi_sakti_persentase_tkdn : null),
                    'kategori_pdn' => $item->interkoneksi_sakti_kategori_pdn ? $item->interkoneksi_sakti_kategori_pdn : ($interkoneksi_sakti_kategori_pdn ? $interkoneksi_sakti_kategori_pdn : null),
                ];
                InterkoneksiSaktiDetailBarang::create($data);
                GenerateInterkoneksiSaktiDetailBarang::onJobSuccess($this->generateInterkoneksiSaktiDetailBarangId);
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            $message = "ERROR GENERATE INTERKONEKSI SAKTI DETAIL BARANG ID: {$this->generateInterkoneksiSaktiDetailBarangId}) : " . $exception->getMessage();
            GenerateInterkoneksiSaktiDetailBarang::onJobFail($this->generateInterkoneksiSaktiDetailBarangId, $message);
        }
    }

    public function failed(?\Throwable $exception): void
    {
        $message = "ERROR GENERATE INTERKONEKSI SAKTI DETAIL BARANG ID: {$this->generateInterkoneksiSaktiDetailBarangId}) : " . $exception->getMessage();
        GenerateInterkoneksiSaktiDetailBarang::onJobFail($this->generateInterkoneksiSaktiDetailBarangId, $message);
    }
}
