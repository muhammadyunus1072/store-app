<div class="card mb-4">
    <div class="card-header">
        <div class='card-title row'>
            <div class='col-auto'>
                <h4>Informasi Persetujuan</h4>
            </div>
            <div class='col-auto'>
                {!! $status !!}
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class='row align-items-center'>
            <div class="col-md-auto mb-4">
                Sumber:
            </div>

            <div class="col-md mb-4">
                {!! $this->remarksUrlButton !!}
            </div>
        </div>

        <div class="row mb-2">
            <div class='col-auto'>
                @if ($isSequentially)
                    <i class="ki-solid ki-check-square text-success fs-1"></i>
                @else
                    <i class="ki-solid ki-cross-square text-danger fs-1"></i>
                @endif
            </div>

            <label class="col">
                Persetujuan Harus Berurutan
            </label>
        </div>

        <div class="row mb-2">
            <div class='col-auto'>
                @if ($isDoneWhenAllSubmitted)
                    <i class="ki-solid ki-check-square text-success fs-1"></i>
                @else
                    <i class="ki-solid ki-cross-square text-danger fs-1"></i>
                @endif
            </div>

            <label class="col">
                Persetujuan Selesai Jika Seluruh Memberikan Respon
            </label>
        </div>

        <table class='table table-bordered'>
            <tr>
                <th>Urutan</th>
                <th>Penyetuju</th>
                <th>Status</th>
            </tr>

            @foreach ($approvalUsers as $user)
                <tr>
                    <td>{{ $user['position'] }}</td>
                    <td>{{ $user['name'] }}</td>
                    <td>{!! $user['status_submitted'] !!}</td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
