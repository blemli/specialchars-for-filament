<?php

use Blemli\SpecialChars\Commands\UninstallCommand;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\File;

function fakePublishedPaths(): array
{
    return [
        config_path('specialchars-for-filament.php'),
        lang_path('vendor/specialchars-for-filament/de/specialchars.php'),
        resource_path('views/vendor/specialchars-for-filament/dropdown.blade.php'),
    ];
}

function publishFakeArtifacts(): array
{
    foreach (fakePublishedPaths() as $path) {
        File::ensureDirectoryExists(dirname($path));
        File::put($path, '<?php return [];');
    }

    return fakePublishedPaths();
}

afterEach(function () {
    foreach (fakePublishedPaths() as $path) {
        File::delete($path);
    }

    File::deleteDirectory(lang_path('vendor/specialchars-for-filament'));
    File::deleteDirectory(resource_path('views/vendor/specialchars-for-filament'));
    File::deleteDirectory(app_path('Providers/Filament'));
});

it('removes every published artifact on uninstall', function () {
    $paths = publishFakeArtifacts();

    $this->artisan('specialchars:uninstall', ['--force' => true])->assertSuccessful();

    foreach ($paths as $path) {
        expect(File::exists($path))->toBeFalse($path . ' should have been removed');
    }
});

it('points to panel providers that still register the plugin', function () {
    $provider = app_path('Providers/Filament/AdminPanelProvider.php');
    File::ensureDirectoryExists(dirname($provider));
    File::put($provider, "<?php\n\n// ...\n\$panel->plugin(SpecialCharsPlugin::make());\n");

    $this->artisan('specialchars:uninstall', ['--force' => true])
        ->expectsOutputToContain('SpecialCharsPlugin is still registered')
        // Basename only — File::allFiles() returns native separators, so a
        // full-path assertion breaks on Windows.
        ->expectsOutputToContain('AdminPanelProvider.php:4')
        ->assertSuccessful();
});

it('loops until the registration is gone and never edits the provider', function () {
    $provider = app_path('Providers/Filament/AdminPanelProvider.php');
    File::ensureDirectoryExists(dirname($provider));
    File::put($provider, "<?php\n\n// ...\n\$panel->plugin(SpecialCharsPlugin::make());\n");
    $before = File::get($provider);

    $this->artisan('specialchars:uninstall')
        // Claimed removed, but the re-scan still finds it — warns again.
        ->expectsConfirmation('Removed it?', 'yes')
        ->expectsConfirmation('Removed it?', 'no')
        ->expectsConfirmation('Run "composer remove blemli/specialchars-for-filament" now?', 'no')
        ->assertSuccessful();

    expect(File::get($provider))->toBe($before);
});

it('exits the loop once the registration disappears', function () {
    $command = new class extends UninstallCommand
    {
        public array $scans = [];

        protected function findPluginRegistrations(): array
        {
            return array_shift($this->scans) ?? [];
        }
    };
    $command->scans = [[app_path('Providers/Filament/AdminPanelProvider.php') . ':4'], []];

    app(Kernel::class)->registerCommand($command);

    $this->artisan('specialchars:uninstall')
        ->expectsConfirmation('Removed it?', 'yes')
        ->expectsOutputToContain('No SpecialCharsPlugin registration left')
        ->expectsConfirmation('Run "composer remove blemli/specialchars-for-filament" now?', 'no')
        ->assertSuccessful();
});

it('never asks under --force and never runs composer', function () {
    $provider = app_path('Providers/Filament/AdminPanelProvider.php');
    File::ensureDirectoryExists(dirname($provider));
    File::put($provider, "<?php\n\n\$panel->plugin(SpecialCharsPlugin::make());\n");

    $this->artisan('specialchars:uninstall', ['--force' => true])
        ->expectsOutputToContain('Finish with: composer remove blemli/specialchars-for-filament')
        ->assertSuccessful();
});

it('prints english output regardless of the app locale', function () {
    app()->setLocale('de');
    publishFakeArtifacts();

    $this->artisan('specialchars:uninstall', ['--force' => true])
        ->expectsOutputToContain('The following will be removed:')
        ->expectsOutputToContain('specialchars-for-filament was uninstalled. Finish with: composer remove blemli/specialchars-for-filament')
        ->assertSuccessful();
});
