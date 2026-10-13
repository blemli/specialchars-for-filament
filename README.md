# specialchars

<p class="filament-hidden">The characters a keyboard lacks, one click away on any Filament text field.</p>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/blemli/specialchars-for-filament.svg?style=flat-square)](https://packagist.org/packages/blemli/specialchars-for-filament) [![Tests](https://img.shields.io/github/actions/workflow/status/blemli/specialchars-for-filament/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/blemli/specialchars-for-filament/actions?query=workflow%3Atests+branch%3Amain) [![Code style](https://img.shields.io/github/actions/workflow/status/blemli/specialchars-for-filament/fix-code-style.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/blemli/specialchars-for-filament/actions?query=workflow%3A%22Fix+code+style%22+branch%3Amain) [![Total Downloads](https://img.shields.io/packagist/dt/blemli/specialchars-for-filament.svg?style=flat-square)](https://packagist.org/packages/blemli/specialchars-for-filament)

«60x90» should read «60×90», a filter is «⌀ 77 mm», a reflector «120°» — none of them on a Swiss keyboard. `->specialChars()` puts the missing characters on the field: one button each, or one dropdown when there are many, inserted at the caret without a round trip. No existing Filament package does this for a `TextInput` (awcodes/typebar targets the Markdown editor).

```bash
composer require blemli/specialchars-for-filament
php artisan specialchars-for-filament:install -n
```

```php
TextInput::make('name')->specialChars();                 // the config's list: × ⌀ ° µ – …
TextInput::make('width')->specialChars(['×', 'U+2300']); // two → inline buttons
Textarea::make('notes')->specialChars(['–', '…', '«', '»']); // hint actions; more than two → one dropdown
```

Defaults for the whole panel: `->plugin(SpecialCharsPlugin::make()->chars(['×', '⌀'])->inlineUpTo(3)->triggerLabel('Ω'))`, or `config/specialchars-for-filament.php`. Characters are the character itself, `'U+2300'` or `0x2300`; with ext-intl the tooltips carry the Unicode name. Ships in English and German. Remove it with `php artisan specialchars:uninstall`.

## License

MIT — see [LICENSE.md](LICENSE.md).
