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
            'name' => [
                'show' => true,
                'render' => function($item, $name, $index, $key, $model)
                {

                    $id = $item['id'];
                    return "<input type='text' class='form-control' wire:key=\"$key\" wire:model.blur=\"$model\"/> ";
                }
            ],
            'is_active' => [
                'show' => true,
                'default' => false,
                'render' => function($item, $name, $index, $key, $model)
                {
                    $id = $item['id'];
                    return "<input class='form-check-input' type='checkbox'
                    wire:model.blur=\"$model\"
                    wire:key=\"$key\"
                    id=\"$model\">
                <label class='form-check-label ms-2 mt-1' for=\"$model\">
                    Aktif
                </label>";
                },
                'searchOperator' => '=',
                'searchDefault' => false,
                'searchRender' => function($item, $name, $model)
                {
                    return "<input class='form-check-input' type='checkbox'
                        wire:model.live.debounce.300ms=\"$model\"
                        id=\"$model\">
                    <label class='form-check-label ms-2 mt-1' for=\"$model\">
                        Aktif
                    </label>";
                },
            ],
            'created_by' => [
                'show' => false,
            ],
            'created_at' => [
                'show' => true,
                'render' => function($item, $name, $index, $key, $model)
                {
                    
                    if(is_numeric($item['id']))
                    {
                        $this->tableData[$item['id']][$name] = Carbon::parse($item['created_at'])->timezone('Asia/Jakarta')->format('Y-m-d');
                    }
                    $id = $item['id'];
                    return "<input type='date' class='form-control' wire:key=\"$key\" wire:model.blur=\"$model\"/> ";
                }
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
