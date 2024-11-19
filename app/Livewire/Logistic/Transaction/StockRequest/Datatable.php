<?php

namespace App\Livewire\Logistic\Transaction\StockRequest;

use App\Helpers\Core\UserStateHandler;
use Carbon\Carbon;
use App\Helpers\General\Alert;
use App\Permissions\AccessLogistic;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Traits\Livewire\WithDatatable;
use App\Permissions\PermissionHelper;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Core\User\UserRepository;
use App\Repositories\Logistic\Transaction\StockRequest\StockRequestRepository;
use App\Settings\SettingCore;
use App\Settings\SettingLogistic;

class Datatable extends Component
{
    use WithDatatable;

    public $isCanUpdate;
    public $isCanDelete;

    public $settingMultipleCompany;
    public $settingApprovalKeyStockRequest;

    // Delete Dialog
    public $targetDeleteId;

    // Filter
    public $dateStart;
    public $dateEnd;
    public $companyId;
    public $warehouseId;

    /*
    | WITH DATATABLE
    */
    public function onMount()
    {
        $this->sortDirection = 'desc';

        $authUser = UserRepository::authenticatedUser();
        $this->isCanUpdate = $authUser->hasPermissionTo(PermissionHelper::transform(AccessLogistic::STOCK_REQUEST, PermissionHelper::TYPE_UPDATE));
        $this->isCanDelete = $authUser->hasPermissionTo(PermissionHelper::transform(AccessLogistic::STOCK_REQUEST, PermissionHelper::TYPE_DELETE));

        $this->settingMultipleCompany = SettingCore::get(SettingCore::MULTIPLE_COMPANY);
        $this->settingApprovalKeyStockRequest = SettingLogistic::get(SettingLogistic::APPROVAL_KEY_STOCK_REQUEST);

        $userState = UserStateHandler::get();
        $this->companyId = $userState['company_id'];
        $this->warehouseId = $userState['warehouse_id'];
    }

    public function getColumns(): array
    {
        $columns =  [
            [
                'name' => 'Aksi',
                'sortable' => false,
                'searchable' => false,
                'render' => function ($item) {
                    $id = Crypt::encrypt($item->id);

                    $showUrl = route('stock_request.show', $id);
                    $showHtml = "<div class='col-auto mb-2'>
                        <a class='btn btn-info btn-sm' href='$showUrl'>
                            <i class='ki-solid ki-eye fs-1'></i>
                            Lihat
                        </a>
                    </div>";

                    $editHtml = "";
                    if ($this->isCanUpdate) {
                        $editUrl = route('stock_request.edit', $id);
                        $editHtml = "<div class='col-auto mb-2'>
                            <a class='btn btn-primary btn-sm' href='$editUrl'>
                                <i class='ki-duotone ki-notepad-edit fs-1'>
                                    <span class='path1'></span>
                                    <span class='path2'></span>
                                </i>
                                Ubah
                            </a>
                        </div>";
                    }

                    $destroyHtml = "";
                    if ($this->isCanDelete) {
                        $destroyHtml = "<div class='col-auto mb-2'>
                            <button class='btn btn-danger btn-sm m-0' 
                                wire:click=\"showDeleteDialog('$id')\">
                                <i class='ki-duotone ki-trash fs-1'>
                                    <span class='path1'></span>
                                    <span class='path2'></span>
                                    <span class='path3'></span>
                                    <span class='path4'></span>
                                    <span class='path5'></span>
                                </i>
                                Hapus
                            </button>
                        </div>";
                    }

                    $html = "<div class='row'>
                        $showHtml $editHtml $destroyHtml 
                    </div>";

                    return $html;
                },
            ],
            [
                'key' => 'number',
                'name' => 'Nomor',
            ]
        ];

        $columns[] = [
            'searchable' => false,
            'key' => 'destination_warehouse_name',
            'name' => 'Gudang Peminta',
        ];

        if ($this->settingMultipleCompany) {
            $columns[] = [
                'sortable' => false,
                'searchable' => false,
                'key' => 'destination_company_name',
                'name' => 'Perusahaan',
            ];
        }

        $columns[] = [
            'key' => 'source_warehouse_name',
            'name' => 'Gudang Diminta',
        ];

        if ($this->settingMultipleCompany) {
            $columns[] = [
                'sortable' => false,
                'searchable' => false,
                'key' => 'source_company_name',
                'name' => 'Perusahaan',
            ];
        }

        $columns[] = [
            'key' => 'transaction_date',
            'name' => 'Tanggal Penerimaan',
            'render' => function ($item) {
                return Carbon::parse($item->transaction_date)->translatedFormat('d F Y');
            }
        ];

        $columns[] = [
            'key' => 'note',
            'name' => 'Catatan',
        ];

        $columns[] = [
            'sortable' => false,
            'searchable' => false,
            'name' => 'Status Proses Stok',
            'render' => function ($item) {
                return $item->transactionStockStatus();
            }
        ];

        if ($this->settingApprovalKeyStockRequest) {
            $columns[] = [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Status Persetujuan',
                'render' => function ($item) {
                    return $item->approvalUrlButton();
                }
            ];
        }

        return $columns;
    }

    public function getQuery(): Builder
    {
        return StockRequestRepository::datatable(
            $this->dateStart,
            $this->dateEnd,
            warehouseId: $this->warehouseId ? Crypt::decrypt($this->warehouseId) : null,
            companyId: $this->companyId ? Crypt::decrypt($this->companyId) : null,
        );
    }

    public function getView(): string
    {
        return 'livewire.logistic.transaction.stock-request.datatable';
    }

    /*
    | DELETE DIALOGUE
    */
    #[On('on-delete-dialog-confirm')]
    public function onDialogDeleteConfirm()
    {
        if (!$this->isCanDelete || $this->targetDeleteId == null) {
            return;
        }

        StockRequestRepository::delete(Crypt::decrypt($this->targetDeleteId));
        Alert::success($this, 'Berhasil', 'Data berhasil dihapus');
    }

    #[On('on-delete-dialog-cancel')]
    public function onDialogDeleteCancel()
    {
        $this->targetDeleteId = null;
    }

    public function showDeleteDialog($id)
    {
        $this->targetDeleteId = $id;

        Alert::confirmation(
            $this,
            Alert::ICON_QUESTION,
            "Hapus Data",
            "Apakah Anda Yakin Ingin Menghapus Data Ini ?",
            "on-delete-dialog-confirm",
            "on-delete-dialog-cancel",
            "Hapus",
            "Batal",
        );
    }
}
