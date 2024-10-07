<?php

namespace Database\Seeders\Logistic;

use App\Helpers\Logistic\StockHelper;
use App\Models\Core\Setting\Setting;
use App\Models\Finance\Master\Tax;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'name' => Setting::NAME_LOGISTIC,
            'setting' => json_encode(
                [
                    'product_code' => false,
                    'product_batch' => false,
                    'product_expired_date' => false,
                    'product_attachment' => false,
                    'product_substract_stock_method' => StockHelper::SUBSTRACT_STOCK_METHOD_FIFO,
                    'approval_key_stock_request' => "",
                    'approval_key_stock_expense' => "",
                    'tax_ppn_good_receive_id' => Tax::where('type', Tax::TYPE_PPN)->first()->id,
                ]
            ),
        ];

        Setting::create($data);
    }
}
