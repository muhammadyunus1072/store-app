<div>
    <div wire:offline.class="d-none" class='mt-2'>
        <div class="d-flex flex-column flex-center">
            <i class="ki-solid ki-notification-circle text-success fs-2"></i>
            <div class="fw-semibold text-success" style="font-size: 12x;">
                Online
            </div>
        </div>
    </div>
    <div wire:offline.class.remove="d-none" class='mt-2 d-none'>
        <div class="d-flex flex-column flex-center">
            <i class="ki-solid ki-notification-circle text-danger fs-2"></i>
            <div class="fw-semibold text-danger" style="font-size: 12x;">
                Offline
            </div>
        </div>
    </div>
</div>
