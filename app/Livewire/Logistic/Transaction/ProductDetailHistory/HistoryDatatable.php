<?php

namespace App\Livewire\Logistic\Transaction\ProductDetailHistory;

use App\Helpers\General\NumberFormatter;
use Livewire\Component;
use App\Repositories\Logistic\Transaction\ProductDetail\ProductDetailHistoryRepository;
use App\Settings\SettingCore;
use App\Settings\SettingLogistic;
use App\Traits\Livewire\WithDatatable;
use Carbon\Carbon;

class HistoryDatatable extends Component
{
    use WithDatatable;

    public $remarksIds = [];
    public $remarksType;

    // Helper
    public $isMultipleCompany;
    public $isInputProductCode;
    public $isInputProductExpiredDate;
    public $isInputProductAttachment;
    public $isInputProductBatch;

    public function mount()
    {
        $this->loadSetting();
    }

    public function loadSetting()
    {
        $this->isMultipleCompany = SettingCore::get(SettingCore::MULTIPLE_COMPANY);

        $this->isInputProductCode = SettingLogistic::get(SettingLogistic::INFO_PRODUCT_CODE);
        $this->isInputProductExpiredDate = SettingLogistic::get(SettingLogistic::INFO_PRODUCT_EXPIRED_DATE);
        $this->isInputProductAttachment = SettingLogistic::get(SettingLogistic::INFO_PRODUCT_ATTACHMENT);
        $this->isInputProductBatch = SettingLogistic::get(SettingLogistic::INFO_PRODUCT_BATCH);
    }

    public function getColumns(): array
    {
        $columns = [
            [
                'searchable' => false,
                'key' => 'product_detail_histories.transaction_date',
                'name' => 'Tgl',
                'render' => function ($item) {
                    return Carbon::parse($item->transaction_date)->translatedFormat('d F Y');
                }
            ],
            [
                'key' => 'products.name',
                'name' => 'Nama',
                'render' => function ($item) {
                    return $item->product_name;
                }
            ],
            [
                'searchable' => false,
                'key' => 'product_detail_histories.quantity',
                'name' => 'Jml',
                'render' => function ($item) {
                    return NumberFormatter::format($item->quantity);
                }
            ],
            [
                'searchable' => false,
                'sortable' => false,
                'key' => 'unit_details.name',
                'name' => 'Satuan',
                'render' => function ($item) {
                    return $item->unit_detail_name;
                }
            ],
            [
                'searchable' => false,
                'key' => 'product_details.price',
                'name' => 'Nilai Per Satuan',
                'render' => function ($item) {
                    return NumberFormatter::format($item->price);
                }
            ],
            [
                'searchable' => false,
                'key' => 'product_details.entry_date',
                'name' => 'Tgl Masuk',
                'render' => function ($item) {
                    return Carbon::parse($item->entry_date)->translatedFormat('d F Y');
                }
            ],
            [
                'key' => 'warehouses.name',
                'name' => 'Gudang',
                'render' => function ($item) {
                    return $item->warehouse_name;
                }
            ],
            [
                'key' => 'product_detail_histories.remarks_note',
                'name' => 'Remarks Note',
                'render' => function ($item) {
                    return $item->remarks_note;
                }
            ]
        ];

        if ($this->isMultipleCompany) {
            $columns[] = [
                'key' => 'companies.name',
                'name' => 'Perusahaan',
                'render' => function ($item) {
                    return $item->company_name;
                }
            ];
        }

        if ($this->isInputProductCode || $this->isInputProductExpiredDate || $this->isInputProductAttachment || $this->isInputProductBatch) {
            $columns[] = [
                'searchable' => false,
                'sortable' => false,
                'name' => 'Informasi',
                'render' => function ($item) {
                    $html = "<ul>";

                    if ($this->isInputProductCode && !empty($item->code)) {
                        $html .= "<li>Kode: {$item->code}</li>";
                    }
                    if ($this->isInputProductExpiredDate && !empty($item->expired_date)) {
                        $html .= "<li>ED: {$item->expired_date}</li>";
                    }
                    if ($this->isInputProductBatch && !empty($item->batch)) {
                        $html .= "<li>Batch: {$item->batch}</li>";
                    }
                    $html .= "</ul>";

                    if ($this->isInputProductAttachment && count($item->productDetail->attachments) > 0) {
                        $html .= "<label>Lampiran</label><ul>";
                        foreach ($item->productDetail->attachments as $attachment) {
                            $html .= "<li><a target='_blank' class='btn btn-primary' href='{$attachment->getFile()}'>{$attachment->original_file_name}</a></li>";
                        }
                        $html .= "</ul>";
                    }

                    return $html;
                }
            ];
        }

        return $columns;
    }

    public function getQuery()
    {
        return ProductDetailHistoryRepository::datatable($this->remarksIds, $this->remarksType);
    }

    public function getView(): string
    {
        return 'livewire.logistic.transaction.product-detail-histories.history-datatable';
    }
}
