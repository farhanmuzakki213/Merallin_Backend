<div class="flex items-center gap-2">
    {{-- Status Badge --}}
    <span @class([
        'rounded-full px-2 py-0.5 text-xs font-semibold text-white',
        'bg-yellow-500' => $status == 'pending',
        'bg-green-500' => $status == 'approved',
        'bg-red-500' => $status == 'rejected',
    ])>
        {{ ucfirst($status) }}
    </span>

    {{-- Action Buttons for Pending Status --}}
    @if ($status == 'pending')
        <div class="flex items-center gap-1">
            {{-- Approve Button (UPDATED with wire:confirm) --}}
            <button
                wire:click="approvePhoto({{ $tripId }}, '{{ $photoType }}')"
                wire:confirm="Are you sure you want to approve this photo?"
                wire:loading.attr="disabled"
                class="text-green-500 hover:text-green-700"
                title="Approve"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </button>

            {{-- Reject Button (no changes needed) --}}
            <button
                wire:click="openRejectionModal({{ $tripId }}, '{{ $photoType }}')"
                wire:loading.attr="disabled"
                class="text-red-500 hover:text-red-700"
                title="Reject"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    @endif
</div>
