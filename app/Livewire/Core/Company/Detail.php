<?php

namespace App\Livewire\Core\Company;

use Exception;
use App\Helpers\General\Alert;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\Core\Company\CompanyRepository;
use App\Repositories\Core\Company\CompanyWarehouseRepository;


class Detail extends Component
{
    public $objId;

    #[Validate('required', message: 'Nama Produk Harus Diisi', onUpdate: false)]
    public $name;

    public $companyWarehouses = [];
    public $company_warehouse_removes = [];

    public function mount()
    {
        if ($this->objId) {
            $id = Crypt::decrypt($this->objId);
            $company = CompanyRepository::findWithDetails($id);

            $this->name = $company->name;

            foreach ($company->companyWarehouses as $company_warehouse) {
                $this->companyWarehouses[] = [
                    'id' => Crypt::encrypt($company_warehouse->warehouse_id),
                    'text' => $company_warehouse->warehouse->name,
                ];
            }
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            $this->redirectRoute('company.edit', $this->objId);
        } else {
            $this->redirectRoute('company.create');
        }
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('company.index');
    }

    public function selectWarehouse($data)
    {
        $this->companyWarehouses[] = [
            'id' => $data['id'],
            'text' => $data['text'],
        ];
    }

    public function unselectWarehouse($data)
    {
        $index = array_search($data['id'], array_column($this->companyWarehouses, 'id'));
        if ($index !== false) {
            unset($this->companyWarehouses[$index]);
        }
    }

    public function store()
    {
        if (count($this->companyWarehouses) == 0) {
            Alert::fail($this, "Gagal", "Gudang Belum Diinput");
            return;
        }
        $this->validate();

        $validatedData = [
            'name' => $this->name,
        ];

        try {
            DB::beginTransaction();
            if ($this->objId) {
                $objId = Crypt::decrypt($this->objId);
                CompanyRepository::update($objId, $validatedData);
            } else {
                $obj = CompanyRepository::create($validatedData);
                $objId = $obj->id;
            }

            foreach ($this->companyWarehouses as $company_warehouse) {
                CompanyWarehouseRepository::createIfNotExist([
                    'company_id' => $objId,
                    'warehouse_id' => Crypt::decrypt($company_warehouse['id']),
                ]);
            }

            CompanyWarehouseRepository::deleteExcept($objId, array_map(function ($item) {
                return Crypt::decrypt($item['id']);
            }, $this->companyWarehouses));

            DB::commit();

            Alert::confirmation(
                $this,
                Alert::ICON_SUCCESS,
                "Berhasil",
                "Data Berhasil Diperbarui",
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
        return view('livewire.core.company.detail');
    }
}
