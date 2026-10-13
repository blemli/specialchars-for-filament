<?php

namespace Blemli\SpecialChars\Actions;

use Blemli\SpecialChars\Support\InsertScript;
use Blemli\SpecialChars\Support\SpecialChar;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Support\Enums\Size;

/**
 * One button per character: inserts it at the caret of the field's input,
 * client-side only — a server round trip would reset the caret and lose
 * unsaved typing. The input event keeps Livewire's wire:model in step.
 */
class InsertCharAction extends Action
{
    protected SpecialChar | Closure | null $char = null;

    protected Field | Closure | null $field = null;

    public static function getDefaultName(): ?string
    {
        return 'insert';
    }

    public static function for(SpecialChar $char, Field $field): static
    {
        return static::make($char->actionName())->char($char)->field($field);
    }

    public function char(SpecialChar | Closure | null $char): static
    {
        $this->char = $char;

        return $this;
    }

    public function field(Field | Closure | null $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function getChar(): SpecialChar
    {
        return $this->evaluate($this->char) ?? throw new \LogicException('InsertCharAction needs a character — use InsertCharAction::for().');
    }

    public function getField(): ?Field
    {
        return $this->evaluate($this->field);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (): string => $this->getChar()->char)
            ->tooltip(fn (): string => __('specialchars-for-filament::specialchars.insert', ['name' => $this->getChar()->label()]))
            ->link()
            ->color('gray')
            ->size(Size::Small)
            ->livewireClickHandlerEnabled(false)
            ->alpineClickHandler(fn (): string => $this->insertScript());
    }

    public function insertScript(): string
    {
        return InsertScript::for($this->getField(), $this->getChar());
    }
}
