<div class="note-detail">
    <div class="row mb-3">
        <div class="col-md-6">
            <h6 class="text-muted mb-2">{{ __('admin.product') }}</h6>
            @if($note->product)
                <div class="d-flex align-items-center">
                    @if($note->product->getFirstMediaUrl('products'))
                        <img src="{{ $note->product->getFirstMediaUrl('products', 'thumb') }}" 
                             alt="{{ $note->product->name }}" 
                             style="height: 50px; width: 50px; object-fit: cover; border-radius: 8px; margin-inline-end: 10px;">
                    @endif
                    <div>
                        <strong>{{ $note->product->name }}</strong>
                        <br>
                        <small class="text-muted">ID: {{ $note->product->id }}</small>
                    </div>
                </div>
            @else
                <span class="text-muted">{{ __('admin.product_deleted') }}</span>
            @endif
        </div>
        <div class="col-md-6">
            <h6 class="text-muted mb-2">{{ __('admin.user') }}</h6>
            @if($note->user)
                <div>
                    <strong>{{ $note->user->name }}</strong>
                    <br>
                    <small class="text-muted">{{ $note->user->email }}</small>
                    <br>
                    <small class="text-muted">ID: {{ $note->user->id }}</small>
                </div>
            @else
                <span class="text-muted">{{ __('admin.guest') }}</span>
            @endif
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <h6 class="text-muted mb-2">{{ __('admin.date') }}</h6>
            <p class="mb-0">{{ $note->created_at->format('Y-m-d H:i:s') }}</p>
        </div>
        <div class="col-md-6">
            <h6 class="text-muted mb-2">{{ __('admin.note_id') }}</h6>
            <p class="mb-0">#{{ $note->id }}</p>
        </div>
    </div>

    <div class="mb-3">
        <h6 class="text-muted mb-2">{{ __('admin.note') }}</h6>
        <div class="card bg-light p-3" style="min-height: 100px;">
            <p class="mb-0" style="white-space: pre-wrap;">{{ $note->note }}</p>
        </div>
    </div>
</div>
