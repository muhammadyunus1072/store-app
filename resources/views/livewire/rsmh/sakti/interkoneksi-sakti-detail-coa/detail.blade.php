<form wire:submit="store">
    <div class='row'>
        <div class="col-md-auto mt-2">
            <button type="submit" class="btn btn-primary" {{ $isGenerateProcess ? 'disabled' : '' }}>
                @if ($isGenerateProcess)
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Loading ({{ $isGenerateProcess->progress ? $isGenerateProcess->progress : 0 }} / {{ $isGenerateProcess->total }})
                @else
                    <i class="fa fa-sync"></i>
                    Generate Data
                @endif
            </button>
        </div>
    </div>
</form>
