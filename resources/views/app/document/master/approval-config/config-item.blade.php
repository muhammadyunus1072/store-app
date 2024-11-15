@php
    $indexes = explode('.group.', $index);
    $nested_count = count($indexes);
@endphp

<div class="row align-items-end mb-4" style="margin-left: {{ 68 * ($nested_count - 1) }}px;">
    <div class="col-auto {{ $indexes[$nested_count - 1] == 0 ? 'd-none' : '' }}">
        <button type="button" class="btn btn-danger btn-sm " wire:click="removeConfig('{{ $index }}')">
            <i class="fa fa-times"></i>
        </button>
    </div>
    @if ($indexes[$nested_count - 1] != 0)
        <div class="col-md-2">
            <label class='fw-bold'>Penghubung</label>
            <select class="form-select form-select-sm" wire:key="key_logic_{{ $index }}"
                wire:model="config.{{ $index }}.logic">
                @foreach ($configLogicChoice as $configLogicKey => $configLogicValue)
                    <option value="{{ $configLogicKey }}">{{ $configLogicValue }}</option>
                @endforeach
            </select>
        </div>
    @endif
    <div class="col-md-2">
        <label class='fw-bold'>Tipe</label>
        <select class="form-select form-select-sm" wire:key="key_type_{{ $index }}"
            wire:model.live="config.{{ $index }}.type">
            @foreach ($configTypeChoice as $configTypeKey => $configTypeValue)
                <option value="{{ $configTypeKey }}">{{ $configTypeValue }}</option>
            @endforeach
        </select>
    </div>

    @if ($configItem['type'] == App\Models\Document\Master\ApprovalConfig::TYPE_GROUPER)
        <div class="col-auto d-flex">
            <button type="button" class="btn btn-primary btn-sm" wire:click="addConfig('{{ $index }}')">
                <i class="fa fa-plus"></i> Tambah Aturan
            </button>
        </div>
    @else
        <div class="col-md-2">
            <label class='fw-bold'>Kolom</label>
            <input type="text" class="form-control form-control-sm" wire:key="key_column_{{ $index }}"
                wire:model="config.{{ $index }}.column" />
        </div>
        <div class="col-md-2">
            <label class='fw-bold'>Operator</label>
            <select class="form-select form-select-sm" wire:key="key_operator_{{ $index }}"
                wire:model="config.{{ $index }}.operator">
                @foreach ($configOperatorChoice as $configOperatorKey => $configOperatorValue)
                    <option value="{{ $configOperatorKey }}">{{ $configOperatorValue }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class='fw-bold'>Nilai</label>
            <input type="text" class="form-control form-control-sm" wire:key="key_value_{{ $index }}"
                wire:model="config.{{ $index }}.value" />
        </div>
    @endif
</div>

{{-- Render nested configs recursively --}}
@if (isset($configItem['group']) && count($configItem['group']) > 0)
    @foreach ($configItem['group'] as $nestedIndex => $nestedConfigItem)
        @include('app.document.master.approval-config.config-item', [
            'configItem' => $nestedConfigItem,
            'index' => $index . '.group.' . $nestedIndex,
            'key' => $key,
        ])
    @endforeach
@endif
