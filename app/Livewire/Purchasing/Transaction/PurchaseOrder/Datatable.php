<?php

namespace App\Livewire\Purchasing\Transaction\PurchaseOrder;

use App\Helpers\Core\UserStateHandler;
use Carbon\Carbon;
use App\Traits\Livewire\WithDatatable;
use App\Helpers\General\Alert;
use App\Permissions\AccessPurchasing;
use App\Permissions\PermissionHelper;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Core\User\UserRepository;
use App\Repositories\Purchasing\Transaction\PurchaseOrder\PurchaseOrderRepository;
use App\Settings\SettingCore;
use App\Settings\SettingPurchasing;
use Illuminate\Support\Facades\DB;

class Datatable extends Component
{
    use WithDatatable;

    public $isCanUpdate;
    public $isCanDelete;

    public $settingMultipleCompany;
    public $settingPurchaseOrderAddStock;
    public $settingApprovalKeyPurchaseOrder;

    // Delete Dialog
    public $targetDeleteId;

    // Filter
    public $dateStart;
    public $dateEnd;
    public $warehouseId;
    public $companyId;
    public $productIds = [];
    public $supplierIds = [];

    /*
    | WITH DATATABLE
    */
    public function onMount()
    {
        $this->sortDirection = 'desc';

        $authUser = UserRepository::authenticatedUser();
        $this->isCanUpdate = $authUser->hasPermissionTo(PermissionHelper::transform(AccessPurchasing::PURCHASE_ORDER, PermissionHelper::TYPE_UPDATE));
        $this->isCanDelete = $authUser->hasPermissionTo(PermissionHelper::transform(AccessPurchasing::PURCHASE_ORDER, PermissionHelper::TYPE_DELETE));

        $this->settingMultipleCompany = SettingCore::get(SettingCore::MULTIPLE_COMPANY);
        $this->settingPurchaseOrderAddStock = SettingPurchasing::get(SettingPurchasing::PURCHASE_ORDER_ADD_STOCK);
        $this->settingApprovalKeyPurchaseOrder = SettingPurchasing::get(SettingPurchasing::APPROVAL_KEY_PURCHASE_ORDER);

        $userState = UserStateHandler::get();
        $this->companyId = $userState['company_id'];
        $this->warehouseId = $userState['warehouse_id'];
    }

    public function getColumns(): array
    {
        $columns = [
            [
                'name' => 'Aksi',
                'sortable' => false,
                'searchable' => false,
                'render' => function ($item) {
                    $id = Crypt::encrypt($item->id);

                    $showUrl = route('purchase_order.show', $id);
                    $showHtml = "<div class='col-auto mb-2'>
                        <a class='btn btn-info btn-sm' href='$showUrl'>
                            <i class='ki-solid ki-eye fs-1'></i>
                            Lihat
                        </a>
                    </div>";

                    $editHtml = "";
                    if ($this->isCanUpdate) {
                        $editUrl = route('purchase_order.edit', $id);
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
            ],
            [
                'key' => 'transaction_date',
                'name' => 'Tanggal',
                'render' => function ($item) {
                    return Carbon::parse($item->transaction_date)->translatedFormat('d F Y');
                }
            ],
            [
                'key' => 'supplier_name',
                'name' => 'Supplier',
            ],
            [
                'key' => 'no_spk',
                'name' => 'No SPK',
            ],
        ];

        if ($this->settingMultipleCompany) {
            $columns[] = [
                'sortable' => false,
                'searchable' => false,
                'key' => 'company_name',
                'name' => 'Perusahaan',
            ];
        }

        $columns[] = [
            'key' => 'warehouse_name',
            'name' => 'Gudang',
        ];

        if ($this->settingPurchaseOrderAddStock) {
            $columns[] = [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Status Proses Stok',
                'render' => function ($item) {
                    return $item->transactionStockStatus();
                }
            ];
        }

        if ($this->settingApprovalKeyPurchaseOrder) {
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
        return PurchaseOrderRepository::datatable(
            dateStart: $this->dateStart,
            dateEnd: $this->dateEnd,
            warehouseId: $this->warehouseId ? Crypt::decrypt($this->warehouseId) : null,
            companyId: $this->companyId ? Crypt::decrypt($this->companyId) : null,
            productIds: collect($this->productIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray(),
            supplierIds: collect($this->supplierIds)->map(function ($id) {
                return Crypt::decrypt($id);
            })->toArray(),
        );
    }

    public function getView(): string
    {
        return 'livewire.purchasing.transaction.purchase-order.datatable';
    }

    /*
    | DELETE DIALOGUE
    */
    #[On('on-delete-dialog-confirm')]
    public function onDialogDeleteConfirm()
    {
        try {
            DB::beginTransaction();

            if (!$this->isCanDelete || $this->targetDeleteId == null) {
                return;
            }

            PurchaseOrderRepository::delete(Crypt::decrypt($this->targetDeleteId));
            Alert::success($this, 'Berhasil', 'Data berhasil dihapus');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::fail($this, 'Gagal', $e->getMessage());
        }
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
