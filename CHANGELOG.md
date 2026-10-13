# Changelog

All notable changes to `specialchars-for-filament` will be documented in this file.

## v0.1.0 - 2026-10-13

Initial release.

- `->specialChars()` on `TextInput` (inline suffix buttons, one dropdown above the threshold) and `Textarea` (hint actions)
- Characters as the character, `U+XXXX` or an int; Unicode names as tooltips when ext-intl is present
- Client-side insertion at the caret (`setRangeText` + input event) — no round trip, no lost typing
- Panel plugin and config for the default list, the inline threshold and the dropdown trigger
- `specialchars:install` / `specialchars:uninstall`, English and German
