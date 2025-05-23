<div>
    {{-- MODAL UPDATE --}}
    <div class="modal fade" id="modalUpdate" data-backdrop="static" data-keyboard="false" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Data</h5>
                    <button class="btn" type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form wire:submit.prevent="saveBulk">
                    <div class="modal-body">
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input"
                                        type="radio"
                                        id="radioSeluruh"
                                        wire:model.live="isSeluruh"
                                        value="1">
                                    <label class="form-check-label" for="radioSeluruh">
                                        Seluruh
                                        </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input"
                                        type="radio"
                                        id="radioSebagian"
                                        wire:model.live="isSeluruh"
                                        value="0">
                                    <label class="form-check-label" for="radioSebagian">
                                        Sebagian
                                        </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row {{(!$isSeluruh) ? '' : 'd-none'}} mb-2">
                            <div class="col-md-6">
                                <label>Range Baris (Mulai)</label>
                                <input type="text" class="form-control" wire:model.blur="rangeStart" />
                            </div>
                            <div class="col-md-6">
                                <label>(Akhir)</label>
                                <input type="text" class="form-control" wire:model.blur="rangeEnd" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label>Nilai</label>
                                <br>
                                @php
                                     $bulkItem = [];
                                    foreach($columns as $key => $value)
                                    {
                                        $bulkItem[$key] = isset($value['default']) ? $value['default'] : null;
                                    }
                                    $id = Str::random(30);
                                    $bulkItem['id'] = $id;
                                @endphp             
                                @if (isset($columns[$updatedKey]['render']) && is_callable($columns[$updatedKey]['render']))
                                    {!! call_user_func($columns[$updatedKey]['render'], $bulkItem, $updatedKey, 0, 'newData', "value" ) !!}
                                @else
                                    <input type="text" class="form-control" wire:model.blur="value" />
                                @endif
                            </div>
                        </div>
                        
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    

    {{-- DATATABLE --}}
    <div class="row justify-content-between mb-3">
        <div class="col-auto mb-2 {{ $showSelectPageLength ? '' : 'd-none' }}">
            <label>Show</label>
            <select wire:model.change="length" class="form-select">
                @foreach ($lengthOptions as $item)
                    <option value="{{ $item }}">{{ $item }}</option>
                @endforeach
            </select>
        </div>
        <div class="col row align-items-end">
            <button type="button" class="btn btn-success mb-2 w-auto" wire:click="addData"><i class="fa fa-plus"></i>Tambah Data</button>
        </div>
        <div class="col row align-items-end">
            @php
                $newData = collect($tableData)->filter(function ($value, $key) {
                    return !is_numeric($key);
                })->toArray();
            @endphp     
            <button type="button" class="btn btn-success mb-2 w-auto {{(empty($row_updates) && empty($newData)) ? 'd-none' : ''}}" wire:click="save">Simpan Perubahan</button>
        </div>
        <div class="col-sm-6 mb-2 {{ $showKeywordFilter ? '' : 'd-none' }}">
            <label>Pencarian</label>
            <input wire:model.live.debounce.300ms="search" type="text" class="form-control">
        </div>
    </div>

    <div class="position-relative">
        <div wire:loading wire:target="previousPage, nextPage, gotoPage, save">
            <div class="position-absolute w-100 h-100">
                <div class="w-100 h-100" style="background-color: grey; opacity:0.2"></div>
            </div>
            <h5 class="position-absolute shadow bg-white p-2 rounded"
                style="top: 50%;left: 50%;transform: translate(-50%, -50%);">Loading...</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered text-nowrap w-100 h-100">

                <thead>
                    <tr wire:key='datatable_footer'>
                        @foreach ($columns as $name => $col)
                                @if (!is_numeric($name) && (!isset($col['searchable']) || $col['searchable'] == true))
                                    <th>
                                        @if (isset($col['searchRender']) && is_callable($col['searchRender']))
                                            {!! call_user_func($col['searchRender'], $item, $name, "searches.$name") !!}
                                        @else
                                            {!! "<input type='text' class='form-control' placeholder='".ucwords(str_replace('_', ' ', $name))."' wire:model.live.debounce.300ms=\"searches.$name\"/>"  !!}
                                        @endif
                                    </th>
                                @else
                                    <th></th>
                                @endif
                        @endforeach
                    </tr>
                </thead>
                <thead>
                    <tr>
                        @foreach ($columns as $key => $col)
                            <th wire:key='datatable_header_{{ $key }}'>
                                @if (!isset($col['sortable']) || $col['sortable'])
                                    @php $isSortAscending = $key == $sortBy && $sortDirection == 'asc'@endphp
                                    <button type="button" class='btn p-0 m-0'
                                        wire:click="datatableSort('{{ $key }}')">
                                        <div class="fw-bold align-items-center d-flex">
                                            <div class='pe-2'>
                                                {{ isset($col['name']) ? $col['name'] : $key }}
                                            </div>
                                            <div class="d-flex flex-column">
                                                <i
                                                    class="ki-duotone ki-up fs-4 m-0 p-0
                                {{ $isSortAscending ? 'text-dark' : 'text-secondary' }}"></i>
                                                <i
                                                    class="ki-duotone ki-down fs-4 m-0 p-0
                                {{ $isSortAscending ? 'text-secondary' : 'text-dark' }}"></i>

                                                @if(!is_numeric($key))
                                                    <button type="button" class="btn btn-primary btn-sm p-1 ms-3" data-bs-toggle="modal"
                                                    data-bs-target="#modalUpdate"
                                                    wire:click="showData('{{ $key }}')">Update</button>
                                                @endif
                                            </div>
                                        </div>
                                    </button>
                                @else
                                    <div class="fs-6 p-2">
                                        {{ isset($col['name']) ? $col['name'] : $key }}
                                    </div>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @foreach ($tableData as $index => $item)
                        @if (!is_numeric($index))
                            <tr wire:key='datatable_new_row_{{ $index }}'>
                                @foreach ($columns as $name => $col)
                                    @php
                                        $cell_style = '';
                                        if (isset($col['style'])) {
                                            $cell_style = is_callable($col['style'])
                                                ? call_user_func($col['style'], $item, $index)
                                                : $col['style'];
                                            $cell_style = "style='{$cell_class}'";
                                        }

                                        $cell_class = '';
                                        if (isset($col['class'])) {
                                            $cell_class = is_callable($col['class'])
                                                ? call_user_func($col['class'], $item, $index)
                                                : $col['class'];
                                            $cell_class = "class='{$cell_class}'";
                                        }
                                    @endphp

                                    @if (isset($col['render']) && is_callable($col['render']))
                                        <td {!! $cell_class !!} {!! $cell_style !!}>
                                            {{json_encode($item);}}
                                            {{-- {!! call_user_func($col['render'], $item, $name, $index, $name."_".$item['id'], "tableData.".$item['id'].".$name" ) !!} --}}
                                        </td>
                                    @else
                                        <td {!! $cell_class !!} {!! $cell_style !!}>
                                            {!! "<input type='text' class='form-control' wire:key=\"$name"."_".$item['id']."\" wire:model.live=\"tableData.".$item['id'].".$name\"/>"  !!}
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @endif
                    @endforeach
                    @foreach ($data as $index => $item)
                        <tr wire:key='datatable_row_{{ $index }}'>
                            @foreach ($columns as $name => $col)
                                @php
                                    $cell_style = '';
                                    if (isset($col['style'])) {
                                        $cell_style = is_callable($col['style'])
                                            ? call_user_func($col['style'], $item, $index)
                                            : $col['style'];
                                        $cell_style = "style='{$cell_class}'";
                                    }

                                    $cell_class = '';
                                    if (isset($col['class'])) {
                                        $cell_class = is_callable($col['class'])
                                            ? call_user_func($col['class'], $item, $index)
                                            : $col['class'];
                                        $cell_class = "class='{$cell_class}'";
                                    }
                                @endphp

                                @if (isset($col['render']) && is_callable($col['render']))
                                    <td {!! $cell_class !!} {!! $cell_style !!}>
                                        {!! call_user_func($col['render'], $item, $name, $index, $name."_".$item['id'], "tableData.".$item['id'].".$name") !!}
                                    </td>
                                @else
                                    <td {!! $cell_class !!} {!! $cell_style !!}>
                                        {!! "<input type='text' class='form-control' wire:key=\"$name"."_"."$item->id\" wire:model.live=\"tableData.".$item->id.".$name\"/>"  !!}
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered text-nowrap w-100 h-100">

            </table>
        </div>
    </div>

    <div class="row justify-content-end mt-3">
        <div class="col">
            <em>Total Data: {{ $data->total() }}</em>
        </div>
        <div class="col-auto">
            {{ $data->links() }}
        </div>
    </div>
</div>

@push('js')
    <script>
        Livewire.on('onSuccessStore', function() {
            let a = bootstrap.Modal.getInstance($('#modalUpdate'));
        });
        window.addEventListener('beforeunload', (event) => {
            
            let data = JSON.parse(JSON.stringify(@this.get('row_updates')));
            let newData = JSON.parse(JSON.stringify(@this.get('tableData')));
            if ((data && Object.keys(data).length > 0) || (Object.keys(newData).some(key => isNaN(key)))) {
                event.preventDefault();
            }
        });
    </script>
@endpush

@push('css')
    <style>
      input[type=radio] {
            /* Double-sized Checkboxes */
            -ms-transform: scale(1.25);
            /* IE */
            -moz-transform: scale(1.25);
            /* FF */
            -webkit-transform: scale(1.25);
            /* Safari and Chrome */
            -o-transform: scale(1.25);
            /* Opera */
            padding: 10px;
        }
    </style>
@endpush