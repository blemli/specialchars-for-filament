<?php

namespace Blemli\SpecialChars\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

use function Laravel\Prompts\confirm;

class UninstallCommand extends Command
{
    public $signature = 'specialchars:uninstall {--force : Skip all confirmation prompts}';

    public $description = 'Uninstall specialchars-for-filament: remove published files';

    public function handle(): int
    {
        $publishedPaths = array_filter([
            config_path('specialchars-for-filament.php'),
            lang_path('vendor/specialchars-for-filament'),
            resource_path('views/vendor/specialchars-for-filament'),
        ], fn (string $path): bool => File::exists($path));

        if ($publishedPaths === []) {
            $this->info('Nothing published to remove.');
        } else {
            $this->info('The following will be removed:');

            foreach ($publishedPaths as $path) {
                $this->line("  - {$path}");
            }

            if ($this->option('force') || confirm('Delete published config and translations?')) {
                foreach ($publishedPaths as $path) {
                    File::isDirectory($path) ? File::deleteDirectory($path) : File::delete($path);
                }
            }
        }

        $registrations = $this->findPluginRegistrations();

        if ($this->option('force')) {
            $this->warnAboutRegistrations($registrations);
        } else {
            while ($registrations !== []) {
                $this->warnAboutRegistrations($registrations);

                if (! confirm('Removed it?', default: false)) {
                    break;
                }

                $registrations = $this->findPluginRegistrations();
            }

            if ($registrations === []) {
                $this->info('No SpecialCharsPlugin registration left.');
            }
        }

        if (! $this->option('force') && confirm('Run "composer remove blemli/specialchars-for-filament" now?', default: $registrations === [])) {
            Process::path(base_path())
                ->forever()
                ->run(['composer', 'remove', 'blemli/specialchars-for-filament'], fn (string $type, string $output) => $this->output->write($output));

            $this->info('specialchars-for-filament was uninstalled.');
        } else {
            $this->info('specialchars-for-filament was uninstalled. Finish with: composer remove blemli/specialchars-for-filament');
        }

        return self::SUCCESS;
    }

    /**
     * @param  array<string>  $registrations
     */
    protected function warnAboutRegistrations(array $registrations): void
    {
        if ($registrations === []) {
            return;
        }

        $this->warn('SpecialCharsPlugin is still registered in your panel provider(s) — remove the ->plugin(SpecialCharsPlugin::make()...) call or the app will crash after composer remove:');

        foreach ($registrations as $location) {
            $this->line("  - {$location}");
        }
    }

    /**
     * Find SpecialCharsPlugin registrations in the app's providers so the
     * user can remove them before the package code disappears. The file is
     * never edited automatically — that is the user's code.
     *
     * @return array<string>
     */
    protected function findPluginRegistrations(): array
    {
        $locations = [];

        if (! File::isDirectory(app_path('Providers'))) {
            return $locations;
        }

        foreach (File::allFiles(app_path('Providers')) as $file) {
            foreach (explode("\n", File::get($file->getPathname())) as $index => $line) {
                if (str_contains($line, 'SpecialCharsPlugin')) {
                    $locations[] = $file->getPathname() . ':' . ($index + 1);
                }
            }
        }

        return $locations;
    }
}
