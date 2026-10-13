<?php

namespace Blemli\SpecialChars;

use Blemli\SpecialChars\Commands\UninstallCommand;
use Closure;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SpecialCharsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'specialchars-for-filament';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasCommands([UninstallCommand::class])
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command
                    ->publishConfigFile()
                    ->endWith(function (InstallCommand $command): void {
                        $command->info('Call ->specialChars() on a TextInput or Textarea. Defaults: config/specialchars-for-filament.php or ->plugin(SpecialCharsPlugin::make()->chars([...])).');
                    });
            });
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(SpecialChars::class);
    }

    public function packageBooted(): void
    {
        $this->registerMacros();
    }

    /**
     * ->specialChars() on a TextInput (inline suffix buttons or one suffix
     * dropdown) and on a Textarea (hint actions — a textarea has no affix).
     */
    protected function registerMacros(): void
    {
        TextInput::macro('specialChars', function (iterable | Closure | null $chars = null, ?Closure $configure = null): TextInput {
            // Macros are bound to the field at call time; phpstan only sees the provider.
            /** @var TextInput $input */
            $input = $this; // @phpstan-ignore varTag.nativeType

            return $input->suffixActions(app(SpecialChars::class)->actionsFor($input, $chars, $configure), isInline: true);
        });

        Textarea::macro('specialChars', function (iterable | Closure | null $chars = null, ?Closure $configure = null): Textarea {
            /** @var Textarea $textarea */
            $textarea = $this; // @phpstan-ignore varTag.nativeType

            return $textarea->hintActions(app(SpecialChars::class)->actionsFor($textarea, $chars, $configure));
        });
    }
}
