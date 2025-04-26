<?php

namespace App\Livewire\Sales\Master\PaymentMethod;

use App\Helpers\General\Alert;
use App\Models\Sales\Master\PaymentMethod;
use App\Permissions\AccessSales;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Traits\Livewire\WithDatatable;
use App\Permissions\PermissionHelper;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Core\User\UserRepository;
use App\Repositories\Sales\Master\PaymentMethod\PaymentMethodRepository;

class Datatable extends Component
{
    use WithDatatable;

    public $isCanUpdate;
    public $isCanDelete;
    public $isCanUpdateBookingTime;
    public $isCanUpdateDetail;

    // Delete Dialog
    public $targetDeleteId;

    public function onMount()
    {
        $authUser = UserRepository::authenticatedUser();
        $this->isCanUpdate = $authUser->hasPermissionTo(PermissionHelper::transform(AccessSales::PAYMENT_METHOD, PermissionHelper::TYPE_UPDATE));
    }

    #[On('on-delete-dialog-confirm')]
    public function onDialogDeleteConfirm()
    {
        if (!$this->isCanDelete || $this->targetDeleteId == null) {
            return;
        }
        
        PaymentMethodRepository::delete(Crypt::decrypt($this->targetDeleteId));
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

    public function getColumns(): array
    {
        return [
            [
                'name' => 'Aksi',
                'sortable' => false,
                'searchable' => false,
                'render' => function ($item) {

                    $editHtml = "";
                    $id = Crypt::encrypt($item->id);
                    if ($this->isCanUpdate) {
                        $editUrl = route('payment_method.edit', $id);
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
                        $editHtml $destroyHtml 
                    </div>";

                    return $html;
                },
            ],
            [
                'key' => 'name',
                'name' => 'Nama Metode Pembayaran',
            ],
            [
                'key' => 'code',
                'name' => 'Kode',
            ],
            [
                'key' => 'type',
                'name' => 'Jenis Biaya Admin',
                'render' => function($item)
                {
                    return PaymentMethod::TYPE_CHOICE[$item->type];
                }
            ],
            [
                'key' => 'amount',
                'name' => 'Nilai Biaya Admin',
                'render' => function($item)
                {
                    return PaymentMethod::TYPE_FIXED === $item->type ? "Rp ".numberFormat($item->amount) : numberFormat($item->amount)." %";
                }
            ],
            [
                'key' => 'is_active',
                'name' => 'Aktif',
                'render' => function($item)
                {
                    return $item->is_active ? "<span class='badge badge-success'>Aktif</span>" : "<span class='badge badge-danger'>Tidak Aktif</span>";
                }
            ],
        ];
    }

    public function getQuery(): Builder
    {
        return PaymentMethodRepository::datatable();
    }

    public function getView(): string
    {
        return 'livewire.sales.master.payment-method.datatable';
    }
}
