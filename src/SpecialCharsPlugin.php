<?php

namespace Blemli\SpecialChars;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;

/**
 * Optional panel plugin: the defaults for every ->specialChars() in the
 * panel. Without it the config file decides.
 */
class SpecialCharsPlugin implements Plugin
{
    use EvaluatesClosures;

    /**
     * @var iterable<string | int> | Closure | null
     */
    protected iterable | Closure | null $chars = null;

    protected int | Closure | null $inlineUpTo = null;

    protected string | Closure | null $triggerLabel = null;

    protected string | Closure | null $triggerIcon = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function current(): ?static
    {
        $panel = Filament::getCurrentOrDefaultPanel();

        if ($panel === null || ! $panel->hasPlugin('specialchars-for-filament')) {
            return null;
        }

        /** @var static $plugin */
        $plugin = $panel->getPlugin('specialchars-for-filament');

        return $plugin;
    }

    public function getId(): string
    {
        return 'specialchars-for-filament';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}

    /**
     * @param  iterable<string | int> | Closure  $chars
     */
    public function chars(iterable | Closure $chars): static
    {
        $this->chars = $chars;

        return $this;
    }

    public function inlineUpTo(int | Closure $count): static
    {
        $this->inlineUpTo = $count;

        return $this;
    }

    public function triggerLabel(string | Closure | null $label): static
    {
        $this->triggerLabel = $label;

        return $this;
    }

    public function triggerIcon(string | Closure | null $icon): static
    {
        $this->triggerIcon = $icon;

        return $this;
    }

    /**
     * @return iterable<string | int> | null
     */
    public function getChars(): ?iterable
    {
        return $this->evaluate($this->chars);
    }

    public function getInlineUpTo(): ?int
    {
        return $this->evaluate($this->inlineUpTo);
    }

    public function getTriggerLabel(): ?string
    {
        return $this->evaluate($this->triggerLabel);
    }

    public function getTriggerIcon(): ?string
    {
        return $this->evaluate($this->triggerIcon);
    }
}
