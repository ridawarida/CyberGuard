@extends('layouts.app')

@section('title', 'Secure Consultations')

@section('content')
<div class="container py-4">
    <h4 class="mb-3">Secure Consultations</h4>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Tracking Code</th>
                    <th>Messages</th>
                    <th>Last Activity</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($consultations as $consultation)
                    <tr>
                        <td>{{ $consultation->incident->tracking_id ?? '—' }}</td>
                        <td>{{ $consultation->messages_count }}</td>
                        <td>{{ $consultation->updated_at->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('moderator.consultations.show', $consultation) }}"
                               class="btn btn-sm btn-crimson-outline">
                                Open
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No consultations yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Laravel's default pagination view is Tailwind-styled, not
         Bootstrap - naming the bundled view explicitly here avoids
         unstyled links without needing any change to a service
         provider. --}}
    {{ $consultations->links('pagination::bootstrap-5') }}
</div>
@endsection
