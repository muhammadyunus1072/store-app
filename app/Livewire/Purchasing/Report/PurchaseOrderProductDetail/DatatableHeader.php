<?php

namespace App\Livewire\Purchasing\Report\PurchaseOrderProductDetail;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Crypt;
use App\Traits\Livewire\WithDatatableHeader;
use App\Repositories\Purchasing\Report\PurchaseOrderProductDetail\PurchaseOrderProductDetailRepository;

class DatatableHeader extends Component
{
    use WithDatatableHeader;

    public $search;
    public $dateStart;
    public $dateEnd;
    public $supplierIds = [];
    public $productIds = [];
    public $categoryProductIds = [];

    public function getHeaderData()
    {
        $data = PurchaseOrderProductDetailRepository::datatable(
            $this->search,
            $this->dateStart,
            $this->dateEnd,
            productIds: collect($this->productIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray(),
            categoryProductIds: collect($this->categoryProductIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray(),
            supplierIds: collect($this->supplierIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray()
        )->get();

        $total_purchase_order = collect($data)->unique('purchase_order_id')->count();
        $total_qty = $data->sum('converted_quantity');
        $total_value = $data->sum('value');
        $total_tax = collect($data)->map(function ($item) {
            $tax = $item->tax_value ? $item->value * $item->tax_value / 100 : 0;
            return $tax;
        })->sum();
        return [
            [
                "col" => 3,
                "name" => "Jumlah Transaksi",
                "value" => $total_purchase_order
            ],
            [
                "col" => 3,
                "name" => "Jumlah Quantity",
                "value" => $total_qty
            ],
            [
                "col" => 3,
                "name" => "Total Sebelum Pajak",
                "value" => $total_value
            ],
            [
                "col" => 3,
                "name" => "Total Pajak",
                "value" => $total_tax
            ],
            [
                "col" => 3,
                "name" => "Total Keseluruhan",
                "value" => $total_value + $total_tax
            ],
        ];
    }
}
