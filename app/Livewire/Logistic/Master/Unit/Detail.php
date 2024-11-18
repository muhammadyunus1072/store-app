<?php

namespace App\Livewire\Logistic\Master\Unit;

use Exception;
use App\Helpers\General\Alert;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use App\Helpers\General\NumberFormatter;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\Logistic\Master\Unit\UnitRepository;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;


class Detail extends Component
{
    public $objId;

    #[Validate('required', message: 'Nama Harus Diisi', onUpdate: false)]
    public $title;

    public $unitDetails = [];
    public $unit_detail_removes = [];

    public function mount()
    {
        if ($this->objId) {
            $id = Crypt::decrypt($this->objId);
            $unit = UnitRepository::findWithDetails($id);

            $this->title = $unit->title;

            foreach($unit->unitDetails as $index => $unit_detail)
            {
                $this->unitDetails[] = [
                    'id' => Crypt::encrypt($unit_detail->id),
                    'is_main' => false,
                    'key' => Str::random(30),
                    "name" => $unit_detail->name,
                    "value" => NumberFormatter::valueToImask($unit_detail->value),
                ];
            }
        }
    }

    public function addDetail()
    {
        $this->unitDetails[] = [
            'id' => null,
            'is_main' => false,
            'key' => Str::random(30),
            "name" => "",
            "value" => 0,
        ];
    }

    public function removeDetail($index)
    {
        if ($this->unitDetails[$index]['id']) {
            $this->unit_detail_removes[] = $this->unitDetails[$index]['id'];
        }

        unset($this->unitDetails[$index]);
    }

    public function updated($property, $value)
    {
        if (str_contains($property, 'unitDetails')) {
            if (str_contains($property, 'is_main') && $value) {
                $properties = explode('.', $property);
                $this->unitDetails[$properties[1]]['value'] = 1;
                foreach($this->unitDetails as $index => $item)
                {
                    if($index != $properties[1])
                    {
                        $this->unitDetails[$index]['is_main'] = false;
                    }
                }
            }
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            $this->redirectRoute('unit.edit', $this->objId);
        } else {
            $this->redirectRoute('unit.create');
        }
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('unit.index');
    }

    public function store()
    {
        $this->validate();

        if (count($this->unitDetails) == 0) {
            Alert::fail($this, "Gagal", "Baris Satuan Detail Belum Diinput");
            return;
        }

        if (collect($this->unitDetails)->where('is_main', true)->count() !== 1) {
            Alert::fail($this, "Gagal", "Harus ada Satu Satuan Detail yang sebagai Satuan Utama");
            return;
        }

        $validatedData = [
            'title' => $this->title,
        ];

        try {
            DB::beginTransaction();
            if ($this->objId) {
                $objId = Crypt::decrypt($this->objId);
                UnitRepository::update($objId, $validatedData);
            } else {
                $obj = UnitRepository::create($validatedData);
                $objId = $obj->id;
            }

            foreach($this->unitDetails as $index => $unit_detail)
            {
                $validatedData = [
                    'unit_id' => $objId,
                    'name' => $unit_detail['name'],
                    'is_main' => $unit_detail['is_main'],
                    'value' => NumberFormatter::imaskToValue($unit_detail['value']),
                ];

                if ($unit_detail['id']) {
                    UnitDetailRepository::update(Crypt::decrypt($unit_detail['id']), $validatedData);
                } else {
                    UnitDetailRepository::create($validatedData);
                }
            }

            foreach ($this->unit_detail_removes as $item) {
                UnitDetailRepository::delete(Crypt::decrypt($item));
            }

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
        return view('livewire.logistic.master.unit.detail');
    }
}
