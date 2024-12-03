<?php

namespace App\Livewire\Finance\Master\TaxEditor;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Finance\Master\Tax;
use App\Traits\Livewire\WithTableEditor;

class Index extends Component
{
    use WithTableEditor;

    protected static function className(): string
    {
        return Tax::class;
    }

    public function columns() : array
    {
        return [
            'id' => [
                'show' => false,
            ],
            'created_at' => [
                'show' => true,
                'render' => function($item, $name, $index)
                {
                    return "<input type='date' class='form-control' wire:key=\"$name"."_"."$item->id\" wire:model.live=\"tableData.$item->id.".$name."\"/> ";
                }
            ],
            'name' => [
                'show' => true,
                'render' => function($item, $name)
                {
                    return "<input type='text' class='form-control' wire:key=\"$name"."_"."$item->id\" wire:model.live=\"tableData.$item->id.".$name."\"/> ";
                }
            ],
        ];
    }
    public function getTableData($data)
    {
        foreach ($data as $key => $value) {
            if (isset($value['created_at'])) {
                $data[$key]['created_at'] = Carbon::parse($value['created_at'])->timezone('Asia/Jakarta')->format('Y-m-d');
            }
        }
        return $data;
    }

    public function excludeColumns(): array
    {
        return [
            'created_by' => [
                'show' => false,
            ],
            'updated_at' => [
                'show' => false,
            ],
            'updated_by' => [
                'show' => false,
            ],
            'deleted_at' => [
                'show' => false,
            ],
            'deleted_by' => [
                'show' => false,
            ],
        ];
    }
}
