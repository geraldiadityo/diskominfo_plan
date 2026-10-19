<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center justify-start gap-3">
            <x-filament::button type="submit" icon="heroicon-m-check">
                Simpan Perubahan
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>

