<x-filament-panels::page>
    <form wire:submit.prevent>
        {{ $this->form }}
    </form>

    @if($classId && $date)
        <div class="mt-6">
            {{ $this->table }}
        </div>
    @else
        <x-filament::section>
            <div class="text-center py-8 text-gray-500">
                <x-heroicon-o-clipboard-document-check class="w-12 h-12 mx-auto text-gray-400" />
                <p class="mt-2 font-medium">Select a class and date to begin marking</p>
                <p class="text-sm mt-1">Then use the Status column to mark each student.</p>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>