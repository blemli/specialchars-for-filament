<?php

namespace Blemli\SpecialChars;

use Blemli\SpecialChars\Actions\InsertCharAction;
use Blemli\SpecialChars\Actions\SpecialCharsDropdownAction;
use Blemli\SpecialChars\Support\SpecialChar;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;

/**
 * Builds the picker for a field: one inline button per character up to the
 * threshold, one dropdown beyond it. Defaults come from the panel plugin
 * (SpecialCharsPlugin) when registered, from the config otherwise.
 */
class SpecialChars
{
    /**
     * @return list<SpecialChar>
     */
    public function defaultChars(): array
    {
        return SpecialChar::collect(SpecialCharsPlugin::current()?->getChars() ?? config('specialchars-for-filament.chars', []));
    }

    public function inlineUpTo(): int
    {
        return SpecialCharsPlugin::current()?->getInlineUpTo() ?? (int) config('specialchars-for-filament.inline_up_to', 2);
    }

    public function triggerLabel(): ?string
    {
        return SpecialCharsPlugin::current()?->getTriggerLabel() ?? config('specialchars-for-filament.trigger_label');
    }

    public function triggerIcon(): ?string
    {
        return SpecialCharsPlugin::current()?->getTriggerIcon() ?? config('specialchars-for-filament.trigger_icon');
    }

    /**
     * @param  iterable<string | int | SpecialChar> | Closure | null  $chars
     * @param  (Closure(InsertCharAction): void) | null  $configure
     * @return list<Action>
     */
    public function actionsFor(Field $field, iterable | Closure | null $chars = null, ?Closure $configure = null): array
    {
        if ($chars instanceof Closure) {
            $chars = $field->evaluate($chars);
        }

        $chars = $chars === null ? $this->defaultChars() : SpecialChar::collect($chars);

        $actions = array_map(function (SpecialChar $char) use ($field, $configure): InsertCharAction {
            $action = InsertCharAction::for($char, $field);

            if ($configure) {
                $configure($action);
            }

            return $action;
        }, $chars);

        if (count($actions) <= $this->inlineUpTo()) {
            return $actions;
        }

        // Folded: one trigger, a dropdown item per character («× Multiplication sign»).
        $dropdown = SpecialCharsDropdownAction::for($chars, $field);

        if (filled($label = $this->triggerLabel())) {
            $dropdown->label($label);
        }

        if (filled($icon = $this->triggerIcon())) {
            $dropdown->icon($icon);
        }

        return [$dropdown];
    }
}
