<form wire:submit="store">
    <div class='row'>
        <div class="col-md-12 mb-4">
            <label>Nama</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.blur="name" />

            @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="col-md-12 mb-4">
            <button type="button" class='btn btn-primary btn-sm mb-2' wire:click='checkAllAccess(1)'>
                <i class='ki-duotone ki-check fs-1'></i>
                Check Seluruh
            </button>
            <button type="button" class='btn btn-danger btn-sm mb-2' wire:click='checkAllAccess(0)'>
                <i class='ki-duotone ki-cross fs-1'>
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                Uncheck Seluruh
            </button>

            <button type="submit" class="btn btn-success btn-sm mb-2">
                <i class='ki-duotone ki-check fs-1'></i>
                Simpan
            </button>
        </div>

        <nav wire:ignore>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                @php $tabIndex = 0; @endphp
                @foreach ($tabs as $tabName => $tabAccesses)
                    <button class="nav-link {{ $tabIndex == 0 ? 'active' : '' }}" id="nav-{{ $tabIndex }}-tab"
                        data-bs-toggle="tab" data-bs-target="#nav-{{ $tabIndex }}" type="button" role="tab"
                        aria-controls="nav-{{ $tabIndex }}" aria-selected="true">
                        {{ $tabName }}
                    </button>

                    @php $tabIndex++; @endphp
                @endforeach
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            @php $tabIndex = 0; @endphp
            @foreach ($tabs as $tabName => $tabAccesses)
                <div class="tab-pane fade {{ $tabIndex == 0 ? 'show active' : '' }}" id="nav-{{ $tabIndex }}"
                    role="tabpanel" aria-labelledby="nav-{{ $tabIndex }}-tab" wire:ignore.self>
                    <table class='table table-bordered'>
                        <tr>
                            <th>Nama</th>
                            <th>Aksi</th>
                            <th colspan="2">Akses</th>
                        </tr>
                        @foreach ($accesses as $keyAccess => $access)
                            @if (in_array($keyAccess, $tabAccesses))
                                <tr>
                                    <td rowspan="{{ count($access['permissions']) + 1 }}">
                                        {{ $access['name'] }}
                                    </td>

                                    <td rowspan="{{ count($access['permissions']) + 1 }}">
                                        <button type="button" class='btn btn-primary btn-sm mb-2 px-2 py-1'
                                            wire:click="checkAllAccess(1, '{{ $keyAccess }}')">
                                            <i class='ki-duotone ki-check fs-1'></i>
                                            Check Seluruh
                                        </button>
                                        <button type="button" class='btn btn-danger btn-sm mb-2 px-2 py-1'
                                            wire:click="checkAllAccess(0, '{{ $keyAccess }}')">
                                            <i class='ki-duotone ki-cross fs-1'>
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            Uncheck Seluruh
                                        </button>
                                    </td>
                                </tr>

                                @foreach ($access['permissions'] as $keyPermission => $permission)
                                    <tr>
                                        <td>
                                            {{ $permission['translated_name'] }}
                                        </td>
                                        <td>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="1"
                                                    id="permission_{{ $keyAccess }}_{{ $keyPermission }}"
                                                    wire:model='accesses.{{ $keyAccess }}.permissions.{{ $keyPermission }}.is_checked'>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                    </table>
                </div>
                @php $tabIndex++; @endphp
            @endforeach
        </div>
    </div>
</form>
