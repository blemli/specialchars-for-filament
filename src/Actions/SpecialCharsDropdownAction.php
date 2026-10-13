<?php

namespace Blemli\SpecialChars\Actions;

use Blemli\SpecialChars\Support\InsertScript;
use Blemli\SpecialChars\Support\SpecialChar;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Support\Enums\Size;

/**
 * Many characters, one trigger: a Filament dropdown listing them with their
 * names, each item inserting at the caret and closing the menu. A plain
 * Action with its own view — an ActionGroup fits neither the suffix nor the
 * hint slot of a field.
 */
class SpecialCharsDropdownAction extends Action
{
    /**
     * @var list<SpecialChar> | Closure | null
     */
    protected array | Closure | null $chars = null;

    protected Field | Closure | null $field = null;

    public static function getDefaultName(): ?string
    {
        return 'special_chars';
    }

    /**
     * @param  list<SpecialChar>  $chars
     */
    public static function for(array $chars, Field $field): static
    {
        return static::make()->chars($chars)->field($field);
    }

    /**
     * @param  list<SpecialChar> | Closure | null  $chars
     */
    public function chars(array | Closure | null $chars): static
    {
        $this->chars = $chars;

        return $this;
    }

    public function field(Field | Closure | null $field): static
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return list<SpecialChar>
     */
    public function getChars(): array
    {
        return $this->evaluate($this->chars) ?? [];
    }

    public function getField(): ?Field
    {
        return $this->evaluate($this->field);
    }

    public function insertScriptFor(SpecialChar $char): string
    {
        return InsertScript::for($this->getField(), $char, then: 'close()');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (): string => __('specialchars-for-filament::specialchars.trigger'))
            ->tooltip(fn (): string => __('specialchars-for-filament::specialchars.trigger'))
            ->color('gray')
            ->size(Size::Small)
            ->livewireClickHandlerEnabled(false)
            ->view('specialchars-for-filament::dropdown');
    }
}
