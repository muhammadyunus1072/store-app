<div class="card">
    <div class="card-header">
        <h4 class='card-title'>{{ $approvalTitle }}</h4>
    </div>
    <div class="card-body">
        @livewire($approvalView['component'], $approvalView['data'])
    </div>
</div>
