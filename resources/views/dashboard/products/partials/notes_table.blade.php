@if($notes->count() > 0)
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>{{ __('admin.user') ?? 'User' }}</th>
                <th>{{ __('admin.note') ?? 'Note' }}</th>
                <th>{{ __('admin.date') ?? 'Date' }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($notes as $note)
                <tr>
                    <td>
                        @if($note->user)
                            {{ $note->user->name }}
                            <br>
                            <small class="text-muted">{{ $note->user->email }}</small>
                        @else
                            <span class="text-muted">{{ __('admin.guest') ?? 'Guest' }}</span>
                        @endif
                    </td>
                    <td>{{ $note->note }}</td>
                    <td>{{ $note->created_at->format('Y-m-d H:i:s') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="alert alert-info text-center">
        <i class="fa fa-info-circle me-2"></i>
        {{ __('admin.no_notes_found') ?? 'No notes found for this product' }}
    </div>
@endif
