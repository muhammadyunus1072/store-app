<?php

namespace App\Livewire\Core\Permission;

use Exception;
use App\Helpers\Alert;
use App\Helpers\PermissionHelper;
use App\Repositories\Core\User\PermissionRepository;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;


class Detail extends Component
{
    public $objId;

    #[Validate('required', message: 'Nama Harus Diisi', onUpdate: false)]
    public $name;

    #[Validate('required', message: 'Tipe Harus Dipilih', onUpdate: false)]
    public $type = PermissionHelper::TYPE_ALL[0];

    public function mount()
    {
        if ($this->objId) {
            $permission = PermissionRepository::find($this->objId);

            $permissionName = explode(PermissionHelper::SEPARATOR, $permission->name);
            $this->name = $permissionName[0];
            $this->type = $permissionName[1];
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            return;
        }

        $this->name = "";
        $this->type = PermissionHelper::TYPE_ALL[0];
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('permission.index');
    }

    public function store()
    {
        $this->validate();

        $permissionName = PermissionHelper::transform($this->name, $this->type);

        $permission = PermissionRepository::findByName($permissionName);
        if (!empty($permission) && $permission->id != $this->objId) {
            $translatedPermissionName = PermissionHelper::translate($permissionName);
            Alert::fail($this, 'Gagal', "Akses dengan nama {$translatedPermissionName} sudah pernah dibuat");
            return;
        }

        $validatedData = [
            'name' => $permissionName
        ];

        try {
            DB::beginTransaction();
            if ($this->objId) {
                PermissionRepository::update($this->objId, $validatedData);
            } else {
                PermissionRepository::create($validatedData);
            }
            DB::commit();

            Alert::confirmation(
                $this,
                Alert::ICON_SUCCESS,
                "Berhasil",
                "Akses Berhasil Diperbarui",
                "on-dialog-confirm",
                "on-dialog-cancel",
                "Oke",
                "Tutup",
            );
        } catch (Exception $e) {
            DB::rollBack();
            Alert::fail($this, "Gagal", $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.core.permission.detail');
    }
}
