<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @foreach ($this->getWidgets() as $widget)
            @livewire($widget)
        @endforeach
    </div>
</x-filament-panels::page>
