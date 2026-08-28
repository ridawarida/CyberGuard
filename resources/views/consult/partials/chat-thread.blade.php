{{--
    Shared chat thread UI.
    Module 3 feature owner: Johra-E-Jannat Oishy.

    Included by both consult/chat.blade.php (victim) and
    moderator/consultations/show.blade.php, parameterised by $viewerType,
    $pollUrl and $sendUrl - one partial and one JS file instead of two
    near-duplicates, the same reasoning panic.js and calm.js each being a
    single file for their one job.
--}}
<div class="cg-consult-thread"
     data-consultation-thread
     data-poll-url="{{ $pollUrl }}"
     data-send-url="{{ $sendUrl }}"
     data-viewer-type="{{ $viewerType }}"
     data-last-id="{{ optional($consultation->messages->last())->id ?? 0 }}">

    <div class="cg-consult-messages" data-consultation-messages>
        @forelse ($consultation->messages as $message)
            <div class="cg-consult-bubble {{ $message->sender_type === $viewerType ? 'cg-consult-bubble--mine' : 'cg-consult-bubble--theirs' }}">
                <p class="cg-consult-bubble-body">{{ $message->body }}</p>
                <span class="cg-consult-bubble-time">{{ $message->created_at->format('g:i A') }}</span>
            </div>
        @empty
            <p class="text-muted text-center small my-4">No messages yet. Say hello whenever you're ready.</p>
        @endforelse
    </div>

    <form class="cg-consult-form" data-consultation-form>
        <div class="input-group">
            <input type="text"
                   class="form-control"
                   data-consultation-input
                   placeholder="Type a message..."
                   maxlength="2000"
                   autocomplete="off"
                   aria-label="Message">
            <button type="submit" class="btn btn-crimson">Send</button>
        </div>
    </form>
</div>

<style>
    .cg-consult-thread {
        display: flex;
        flex-direction: column;
        height: 60vh;
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }

    .cg-consult-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .cg-consult-bubble {
        max-width: 75%;
        padding: 10px 14px;
        border-radius: 14px;
    }

    .cg-consult-bubble-body {
        margin: 0;
        font-size: 14px;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .cg-consult-bubble-time {
        display: block;
        margin-top: 4px;
        font-size: 10px;
        opacity: 0.7;
    }

    .cg-consult-bubble--mine {
        align-self: flex-end;
        background-color: #DC143C;
        color: #ffffff;
        border-bottom-right-radius: 4px;
    }

    .cg-consult-bubble--theirs {
        align-self: flex-start;
        background-color: #f1f1f1;
        color: #1a1a1a;
        border-bottom-left-radius: 4px;
    }

    .cg-consult-form {
        border-top: 1px solid #eeeeee;
        padding: 12px;
    }

    @media (max-width: 576px) {
        .cg-consult-thread { height: 70vh; }
    }
</style>

@push('scripts')
<script src="{{ asset('js/consultation-chat.js') }}?v=1" defer></script>
@endpush
