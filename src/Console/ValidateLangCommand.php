<?php

namespace YellowThree\Voyager\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ValidateLangCommand extends Command
{
    protected $signature = 'voyager:validate-lang {--fix : Add missing keys as copies from English}';

    protected $description = 'Validate all language files have matching keys against English reference';

    public function handle(): int
    {
        $langPath = __DIR__.'/../../publishable/lang';
        $enPath = "{$langPath}/en";

        if (!is_dir($enPath)) {
            $this->error("English reference directory not found at: {$enPath}");
            return 1;
        }

        $referenceFiles = File::files($enPath);
        $enKeys = [];
        foreach ($referenceFiles as $file) {
            $filename = $file->getFilename();
            $keys = array_keys(require $file->getPathname());
            $enKeys[$filename] = $keys;
        }

        $locales = array_filter(File::directories($langPath), fn($dir) => basename($dir) !== 'en');
        $hasErrors = false;

        foreach ($locales as $localeDir) {
            $locale = basename($localeDir);

            foreach ($enKeys as $filename => $expectedKeys) {
                $localeFile = "{$localeDir}/{$filename}";

                if (!File::exists($localeFile)) {
                    $this->warn("[{$locale}] Missing file: {$filename}");
                    $hasErrors = true;
                    continue;
                }

                $actualKeys = array_keys(require $localeFile);
                $missingKeys = array_diff($expectedKeys, $actualKeys);

                if (!empty($missingKeys)) {
                    $this->warn("[{$locale}] {$filename}: missing keys: " . implode(', ', $missingKeys));
                    $hasErrors = true;

                    if ($this->option('fix')) {
                        $localeData = require $localeFile;
                        $enData = require "{$enPath}/{$filename}";
                        foreach ($missingKeys as $key) {
                            $localeData[$key] = $enData[$key];
                        }
                        File::put($localeFile, '<?php' . PHP_EOL . PHP_EOL . 'return ' . var_export($localeData, true) . ';' . PHP_EOL);
                        $this->info("  → Fixed: added " . count($missingKeys) . " keys to {$locale}/{$filename}");
                    }
                }
            }
        }

        if (!$hasErrors) {
            $this->info('All language files are in sync with English reference.');
        }

        return $hasErrors ? 1 : 0;
    }
}
