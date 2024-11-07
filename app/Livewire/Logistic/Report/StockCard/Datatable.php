<?php

namespace App\Livewire\Logistic\Report\StockCard;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Helpers\General\ExportHelper;
use Illuminate\Support\Facades\Crypt;
use App\Traits\Livewire\WithDatatable;
use App\Helpers\General\NumberFormatter;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Core\User\UserRepository;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Report\StockCard\StockCardRepository;
use App\Repositories\Logistic\Master\CategoryProduct\CategoryProductRepository;

class Datatable extends Component
{
    use WithDatatable;

    public $date_start;
    public $date_end;
    public $products = [];
    public $category_products = [];

    public function onMount()
    {
        $this->date_start = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->date_end = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    #[On('export')]
    public function export($type)
    {
        $fileName = 'Data Kartu Stok ' . Carbon::parse($this->date_start)->format('Y-m-d') . ' sd ' . Carbon::parse($this->date_end)->format('Y-m-d');

        $data = $this->datatableGetProcessedQuery()->get();

        $products = collect($this->products)->map(function ($id) {
            return ProductRepository::find($id)->name;
        });
        $category_products = collect($this->category_products)->map(function ($id) {
            return CategoryProductRepository::find($id)->name;
        });

        return ExportHelper::export(
            $type,
            $fileName,
            $data,
            "app.logistic.report.stock-card.export",
            [
                'date_start' => $this->date_start,
                'date_end' => $this->date_end,
                'products' => $products,
                'category_products' => $category_products,
                'keyword' => $this->search,
                'type' => $type,
                'title' => 'Data Kartu Stok',
            ],
            [
                'size' => 'legal',
                'orientation' => 'portrait',
            ]
        );
    }
    
    public function getColumns(): array
    {
        return [
            [
                'key' => 'transaction_date',
                'name' => 'Tanggal',
            ],
            [
                'key' => 'product_name',
                'name' => 'Nama Produk',
            ],
            [
                'key' => 'unit_detail_name',
                'name' => 'Satuan',
            ],
            [
                'key' => 'start_stock',
                'name' => 'Stok Awal',
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Jumlah',
                'render' => function($item)
                {
                    return NumberFormatter::format(abs($item->quantity));
                }
            ],
            [
                'key' => 'last_stock',
                'name' => 'Stok Akhir',
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Awal',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->start_stock * $item->price);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai',
                'render' => function($item)
                {
                    return NumberFormatter::format(abs($item->quantity * $item->price));
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nilai Akhir',
                'render' => function($item)
                {
                    return NumberFormatter::format($item->last_stock * $item->price);
                }
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Keterangan',
                'render' => function($item)
                {
                    $authUser = UserRepository::authenticatedUser();
                    $url = route($item->remarksTable->remarksTableInfo()['route_name'], Crypt::encrypt($item->remarksMasterTable->id));
                    $button = $authUser->hasPermissionTo($item->remarksTable->remarksTableInfo()['access_name']) ? 
                    " <a class='btn btn-primary btn-sm' href='$url' target='_BLANK'>
                                <i class='ki-duotone ki-notepad-edit fs-1'>
                                    <span class='path1'></span>
                                    <span class='path2'></span>
                                </i>
                                Lihat
                            </a>" :
                    NULL;

                    return $item->remarksTable->remarksTableInfo()['translated_name']." ".$item->remarksMasterTable->number." ".$button;
                }
            ],
        ];
    }

    public function getQuery(): Builder
    {
        return StockCardRepository::datatable($this->search, $this->date_start, $this->date_end, $this->products, $this->category_products);
    }

    public function getView(): string
    {
        return 'livewire.logistic.report.stock-card.datatable';
    }
}
