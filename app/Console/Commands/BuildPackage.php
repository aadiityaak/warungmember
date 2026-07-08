<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

class BuildPackage extends Command
{
    protected $signature = 'build:package {--output= : Output zip path} {--force : Skip confirmation}';

    protected $description = 'Build aplikasi menjadi .zip siap deploy ke server';

    protected bool $isWindows = false;

    public function handle(): int
    {
        $this->isWindows = PHP_OS_FAMILY === 'Windows';

        info('📦 Build package untuk deploy...');

        $output = $this->option('output')
            ?: 'dist/warungmember-'.date('Ymd-His').'.zip';

        // Normalize to OS-native separators
        $output = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $output);

        // Make absolute if relative
        if (! preg_match('#^[a-zA-Z]:#', $output)) {
            $output = base_path($output);
        }

        $distDir = dirname($output);

        if (File::exists($output) && ! $this->option('force')) {
            warning("File {$output} sudah ada. Gunakan --force untuk overwrite.");

            return self::FAILURE;
        }

        if (! File::exists($distDir)) {
            File::makeDirectory($distDir, 0755, true);
        }

        // Step 1: Build frontend
        info('🔨 Build frontend assets...');
        $exitCode = $this->runShell('npm run build');
        if ($exitCode !== 0) {
            $this->error('Frontend build gagal.');

            return self::FAILURE;
        }

        // Step 2: Create zip
        info('📦 Membuat zip...');

        $this->createZip($output);

        if (! file_exists($output)) {
            $this->error('Gagal membuat file zip.');

            return self::FAILURE;
        }

        $size = round(filesize($output) / 1024 / 1024, 2);
        info("✅ Build selesai: {$output} ({$size} MB)");
        info('Cara deploy: extract zip → composer install --no-dev → php artisan migrate --force');

        return self::SUCCESS;
    }

    private function createZip(string $outputPath): void
    {
        if (file_exists($outputPath)) {
            @unlink($outputPath);
            clearstatcache();
        }

        // Write to temp dir first to avoid stale directory handle issues on Windows
        $tmpPath = sys_get_temp_dir().'/'.basename($outputPath);
        if (file_exists($tmpPath)) {
            @unlink($tmpPath);
        }

        $zip = new ZipArchive;

        $result = $zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($result !== true) {
            $this->error("Gagal membuka zip: {$tmpPath} (error code: {$result})");

            return;
        }

        $basePath = realpath(base_path());
        $excludes = $this->getExcludes();

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $count = 0;
        $skipped = 0;
        foreach ($files as $file) {
            $filePath = $file->getRealPath() ?: '';
            if (! $filePath) {
                continue;
            }

            $relativePath = str_replace('\\', '/', substr($filePath, strlen($basePath) + 1));

            if ($this->shouldExclude($relativePath, $excludes)) {
                continue;
            }

            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $content = @file_get_contents($filePath);
                if ($content === false) {
                    $skipped++;

                    continue;
                }
                if (! @$zip->addFromString($relativePath, $content)) {
                    $skipped++;

                    continue;
                }
                $count++;
            }
        }

        if (! @$zip->close()) {
            $this->error('ZipArchive::close() gagal. Status: '.$zip->status.', StatusSys: '.$zip->statusSys);
            @unlink($tmpPath);

            return;
        }

        // Move from temp to destination
        $distDir = dirname($outputPath);
        if (! File::exists($distDir)) {
            File::makeDirectory($distDir, 0755, true);
        }

        if (! @rename($tmpPath, $outputPath)) {
            // Fallback: copy + unlink
            @copy($tmpPath, $outputPath);
            @unlink($tmpPath);
        }

        info("Total {$count} file dipaketkan.".($skipped > 0 ? " ({$skipped} dilewati)" : ''));
    }

    private function getExcludes(): array
    {
        return [
            '.git',
            'node_modules',
            'dist',
            'tests',
            '.env',
            '.phpunit.cache',
            $this->isWindows ? 'vendor/bin' : 'vendor',
            'storage/logs/*',
            'storage/framework/cache/*',
            'storage/framework/sessions/*',
            'storage/framework/views/*',
        ];
    }

    private function shouldExclude(string $path, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (str_contains($pattern, '*')) {
                // Glob pattern
                $regex = '#^'.str_replace(['.', '*'], ['\\.', '.*'], $pattern).'$#';
                if (preg_match($regex, $path)) {
                    return true;
                }
            } else {
                // Prefix match
                if (str_starts_with($path, $pattern)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function runShell(string $command): int
    {
        $output = [];
        $exitCode = 0;

        exec($command.' 2>&1', $output, $exitCode);

        foreach ($output as $line) {
            $this->line(trim($line));
        }

        return $exitCode;
    }
}
