@php
    $statusClasses = [
        'Diterima' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-500',
        'Ditolak' => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-500',
        'Menunggu Persetujuan' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-500',
    ];
@endphp
{{-- Status Manajer --}}
<div class="flex items-center justify-between gap-2">
    <div class="flex items-center gap-2">
        <span class="w-16 shrink-0 text-xs text-gray-500">Manajer:</span>
        <p @class([
            'text-theme-xs rounded-full px-2 py-0.5 font-medium',
            $statusClasses[$lembur->persetujuan_manajer] ?? '',
        ])>
            {{ $lembur->persetujuan_manajer }}
        </p>
    </div>
    @if ($lembur->persetujuan_manajer == 'Menunggu Persetujuan')
        @hasrole('manajer')
            <div class="flex items-center gap-1">
                <button wire:click="askForConfirmation({{ $lembur->id }}, 'approve', 'manajer')"
                    class="flex h-6 w-6 items-center justify-center rounded-md text-emerald-500 hover:bg-emerald-100 dark:hover:bg-emerald-500/15">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12" />
                    </svg> </button>
                <button wire:click="askForConfirmation({{ $lembur->id }}, 'reject', 'manajer')"
                    class="flex h-6 w-6 items-center justify-center rounded-md text-red-500 hover:bg-red-100 dark:hover:bg-red-500/15">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg> </button>
            </div>
        @endhasrole
    @endif
</div>
{{-- Status Direksi --}}
<div class="flex items-center justify-between gap-2">
    <div class="flex items-center gap-2">
        <span class="w-16 shrink-0 text-xs text-gray-500">Direksi:</span>
        <p @class([
            'text-theme-xs rounded-full px-2 py-0.5 font-medium',
            $statusClasses[$lembur->persetujuan_direksi] ?? '',
        ])>
            {{ $lembur->persetujuan_direksi }}
        </p>
    </div>
    @if ($lembur->persetujuan_direksi == 'Menunggu Persetujuan')
        @hasrole('direksi')
            <div class="flex items-center gap-1">
                <button wire:click="askForConfirmation({{ $lembur->id }}, 'approve', 'direksi')"
                    class="flex h-6 w-6 items-center justify-center rounded-md text-emerald-500 hover:bg-emerald-100 dark:hover:bg-emerald-500/15">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12" />
                    </svg> </button>
                <button wire:click="askForConfirmation({{ $lembur->id }}, 'reject', 'direksi')"
                    class="flex h-6 w-6 items-center justify-center rounded-md text-red-500 hover:bg-red-100 dark:hover:bg-red-500/15">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg> </button>
            </div>
        @endhasrole
    @endif
</div>
{{-- Status Akhir --}}
<div class="flex items-center gap-2 border-t border-gray-100 pt-1 dark:border-gray-800">
    <span class="w-16 shrink-0 text-xs font-semibold text-gray-600 dark:text-gray-400">Final:</span>
    <p @class([
        'text-theme-xs rounded-full px-2 py-0.5 font-medium',
        $statusClasses[$lembur->status_lembur] ?? '',
    ])>
        {{ $lembur->status_lembur }}
    </p>
</div>
