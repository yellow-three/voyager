<?php

namespace YellowThree\Voyager\Bread\Sources;

use Illuminate\Support\Facades\File;
use YellowThree\Voyager\Bread\Bread;

class JsonBreadSource
{
    protected string $path;

    public function __construct(string $path)
    {
        $this->path = $path;

        if (!File::exists($this->path)) {
            File::makeDirectory($this->path, 0755, true, true);
        }
    }

    /**
     * Find a BREAD definition by slug.
     *
     * @param string $slug
     * @return Bread|null
     */
    public function find(string $slug): ?Bread
    {
        $filePath = "{$this->path}/{$slug}.json";

        if (!File::exists($filePath)) {
            return null;
        }

        try {
            $content = File::get($filePath);
            $attributes = json_decode($content, true) ?: [];
            return new Bread($attributes);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get all BREAD definitions from JSON storage.
     *
     * @return array<string, Bread>
     */
    public function all(): array
    {
        $breads = [];
        if (!File::exists($this->path)) {
            return [];
        }

        $files = File::files($this->path);

        foreach ($files as $file) {
            if ($file->getExtension() === 'json' && !str_contains($file->getFilename(), '.backup.')) {
                $slug = $file->getBasename('.json');
                $bread = $this->find($slug);
                if ($bread) {
                    $breads[$slug] = $bread;
                }
            }
        }

        return $breads;
    }

    /**
     * Save a BREAD definition and create a backup snapshot.
     *
     * @param Bread $bread
     * @return void
     */
    public function save(Bread $bread): void
    {
        $filePath = "{$this->path}/{$bread->slug}.json";

        // If file already exists, create a backup snapshot
        if (File::exists($filePath)) {
            $timestamp = date('Y-m-d@H-i-s');
            $backupPath = "{$this->path}/{$bread->slug}.backup.{$timestamp}.json";
            File::copy($filePath, $backupPath);

            // Clean older backups to prevent bloat (keep only last 5)
            $this->cleanOldBackups($bread->slug);
        }

        File::put($filePath, json_encode($bread->toArray(), JSON_PRETTY_PRINT));
    }

    /**
     * Delete a BREAD definition file.
     *
     * @param string $slug
     * @return void
     */
    public function delete(string $slug): void
    {
        $filePath = "{$this->path}/{$slug}.json";
        if (File::exists($filePath)) {
            File::delete($filePath);
        }
    }

    /**
     * Keep only the 5 most recent backups for a slug.
     *
     * @param string $slug
     * @return void
     */
    protected function cleanOldBackups(string $slug): void
    {
        $files = File::files($this->path);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'json' && str_starts_with($file->getFilename(), "{$slug}.backup.")) {
                $backups[] = $file;
            }
        }

        if (count($backups) > 5) {
            // Sort by modified time oldest first
            usort($backups, fn($a, $b) => $a->getMTime() <=> $b->getMTime());

            // Delete oldest backups
            $toDeleteCount = count($backups) - 5;
            for ($i = 0; $i < $toDeleteCount; $i++) {
                File::delete($backups[$i]->getPathname());
            }
        }
    }
}
