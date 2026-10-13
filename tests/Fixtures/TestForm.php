<?php

namespace Blemli\SpecialChars\Tests\Fixtures;

use Closure;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * A form with one text input and one textarea; the test decides what
 * ->specialChars() gets through a static configurator.
 */
class TestForm extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public static ?Closure $configure = null;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(['name' => 'Softbox 60', 'notes' => '']);
    }

    public function form(Schema $schema): Schema
    {
        $name = TextInput::make('name');
        $notes = Textarea::make('notes');

        if (static::$configure) {
            (static::$configure)($name, $notes);
        } else {
            $name->specialChars();
            $notes->specialChars();
        }

        return $schema->components([$name, $notes])->statePath('data');
    }

    public function render(): View
    {
        return view('specialchars-tests::test-form');
    }
}
