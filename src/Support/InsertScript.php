<?php

namespace Blemli\SpecialChars\Support;

use Filament\Forms\Components\Field;

/**
 * The one piece of JavaScript in the package: splice a character over the
 * input's selection, caret after it, and tell Livewire. Client-side only —
 * a server round trip would reset the caret and lose unsaved typing.
 */
final class InsertScript
{
    public static function for(?Field $field, SpecialChar $char, string $then = ''): string
    {
        // Single-quoted JS strings only: Filament backslash-escapes double
        // quotes inside an Alpine handler, which would break the expression.
        $id = "'" . addcslashes((string) $field?->getId(), "\\'") . "'";
        $text = "'" . addcslashes($char->char, "\\'") . "'";

        // By id first: a dropdown panel may be floated away from the field.
        return "const el = document.getElementById({$id}) ?? \$el.closest('.fi-fo-field')?.querySelector('input:not([type=hidden]), textarea'); "
            . 'if (el) { const start = el.selectionStart ?? el.value.length; const end = el.selectionEnd ?? start; '
            . "el.setRangeText({$text}, start, end, 'end'); el.dispatchEvent(new Event('input', { bubbles: true })); el.focus(); }"
            . ($then !== '' ? " {$then}" : '');
    }
}
