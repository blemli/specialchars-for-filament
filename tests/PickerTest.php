<?php

use Blemli\SpecialChars\Actions\InsertCharAction;
use Blemli\SpecialChars\Actions\SpecialCharsDropdownAction;
use Blemli\SpecialChars\SpecialChars;
use Blemli\SpecialChars\SpecialCharsPlugin;
use Blemli\SpecialChars\Tests\Fixtures\TestForm;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

use function Pest\Livewire\livewire;

beforeEach(function () {
    bootPanel();
    TestForm::$configure = null;
    view()->addNamespace('specialchars-tests', __DIR__ . '/Fixtures/views');
});

/** The rendered form with attribute entities decoded — the handlers live in x-on:click attributes. */
function renderedForm(): string
{
    return html_entity_decode(livewire(TestForm::class)->html(), ENT_QUOTES | ENT_HTML5);
}

it('renders one inline button per character, labelled with the character, that inserts at the caret', function () {
    TestForm::$configure = function (TextInput $name, Textarea $notes): void {
        $name->specialChars(['×', 'U+2300']);
    };

    $html = renderedForm();

    expect($html)->toMatch('/<button[^>]*>\s*<span[^>]*>\s*×\s*<\/span>\s*<\/button>/')
        ->toMatch('/<button[^>]*>\s*<span[^>]*>\s*⌀\s*<\/span>\s*<\/button>/')
        ->toContain('setRangeText(\'⌀\', start, end, \'end\')')
        ->toContain("document.getElementById('form.name')")
        ->toContain('new Event(\'input\', { bubbles: true })')
        ->toContain('Insert Diameter sign')
        ->not->toContain('mountAction(\'insert_u2300\'');
});

it('folds more than the threshold into one dropdown with named items', function () {
    TestForm::$configure = function (TextInput $name, Textarea $notes): void {
        $name->specialChars(['×', '⌀', '°']);
    };

    $html = renderedForm();

    expect($html)->toContain('fi-dropdown')
        ->toContain('Ω')
        ->toContain('Multiplication sign')
        ->toContain('setRangeText(\'°\', start, end, \'end\')')
        ->toContain('el.focus(); } close()');

    $actions = app(SpecialChars::class)->actionsFor(TextInput::make('x'), ['×', '⌀', '°']);

    expect($actions)->toHaveCount(1)
        ->and($actions[0])->toBeInstanceOf(SpecialCharsDropdownAction::class)
        ->and($actions[0]->getChars())->toHaveCount(3)
        ->and($actions[0]->getLabel())->toBe('Ω');
});

it('takes the default list from the config and puts hint actions on a textarea', function () {
    config()->set('specialchars-for-filament.chars', ['–', '…']);

    $html = renderedForm();

    expect($html)->toContain('setRangeText(\'–\', start, end, \'end\')')
        ->toContain('setRangeText(\'…\', start, end, \'end\')')
        ->toContain("document.getElementById('form.notes')");

    $hints = Textarea::make('notes')->specialChars()->getHintActions();

    expect($hints)->toHaveCount(2)
        ->and($hints[0])->toBeInstanceOf(InsertCharAction::class)
        ->and($hints[0]->getChar()->char)->toBe('–');
});

it('lets the panel plugin override the config', function () {
    config()->set('specialchars-for-filament.chars', ['–']);
    config()->set('specialchars-for-filament.inline_up_to', 2);

    $panel = bootPanel();
    $panel->plugin(SpecialCharsPlugin::make()->chars(['×', '⌀', '°'])->inlineUpTo(5)->triggerLabel('Σ'));

    $manager = app(SpecialChars::class);

    expect(array_map(fn ($char) => $char->char, $manager->defaultChars()))->toBe(['×', '⌀', '°'])
        ->and($manager->inlineUpTo())->toBe(5)
        ->and($manager->triggerLabel())->toBe('Σ')
        ->and($manager->actionsFor(TextInput::make('x')))->toHaveCount(3);
});

it('accepts a closure for the characters and a configurator for every action', function () {
    $actions = app(SpecialChars::class)->actionsFor(
        TextInput::make('x'),
        fn (): array => ['⌀'],
        fn (InsertCharAction $action) => $action->color('warning'),
    );

    expect($actions)->toHaveCount(1)
        ->and($actions[0]->getChar()->char)->toBe('⌀')
        ->and($actions[0]->getColor())->toBe('warning')
        ->and($actions[0]->getName())->toBe('insert_u2300');
});

it('keeps the en and de translations in sync', function () {
    $en = require __DIR__ . '/../resources/lang/en/specialchars.php';
    $de = require __DIR__ . '/../resources/lang/de/specialchars.php';

    expect(array_keys($de))->toBe(array_keys($en));

    foreach ($en as $key => $value) {
        expect($de[$key])->not->toBe($value, "{$key} is not translated");
    }
});

it('installs without a question', function () {
    $this->artisan('specialchars-for-filament:install', ['--no-interaction' => true])->assertSuccessful();

    expect(file_exists(config_path('specialchars-for-filament.php')))->toBeTrue();
});
