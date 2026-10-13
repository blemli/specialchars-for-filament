{{-- The picker as a Filament dropdown: trigger (label and/or icon), one item per character with its name. --}}
<x-filament::dropdown placement="bottom-end" :shift="true">
    <x-slot name="trigger">
        <x-filament::link
            tag="button"
            :color="$action->getColor()"
            :size="$action->getSize()"
            :icon="$action->getIcon()"
            :tooltip="$action->getTooltip()"
            class="fi-specialchars-trigger"
        >
            {{ $action->getLabel() }}
        </x-filament::link>
    </x-slot>

    <x-filament::dropdown.list class="fi-specialchars-list">
        @foreach ($action->getChars() as $char)
            <x-filament::dropdown.list.item
                tag="button"
                x-on:click="{{ $action->insertScriptFor($char) }}"
                :title="$char->notation()"
            >
                <span class="fi-specialchars-char" style="display: inline-block; min-width: 1.5em; font-family: ui-monospace, monospace; text-align: center;">{{ $char->char }}</span>
                {{ $char->label() }}
            </x-filament::dropdown.list.item>
        @endforeach
    </x-filament::dropdown.list>
</x-filament::dropdown>
