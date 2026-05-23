<?php

namespace YellowThree\Voyager\Bread;

use Illuminate\Support\Collection;
use YellowThree\Voyager\Bread\Sources\JsonBreadSource;
use YellowThree\Voyager\Bread\Sources\DatabaseBreadSource;

class BreadManager
{
    protected JsonBreadSource $json;
    protected DatabaseBreadSource $database;

    public function __construct(JsonBreadSource $json, DatabaseBreadSource $database)
    {
        $this->json = $json;
        $this->database = $database;
    }

    /**
     * Find a BREAD definition by slug. Prioritizes JSON over Database.
     *
     * @param string $slug
     * @return Bread|null
     */
    public function find(string $slug): ?Bread
    {
        return $this->json->find($slug) ?? $this->database->find($slug);
    }

    /**
     * Get all BREAD definitions. Prioritizes JSON and merges unique Database ones.
     *
     * @return Collection<string, Bread>
     */
    public function all(): Collection
    {
        $fromJson = collect($this->json->all());
        $fromDb = collect($this->database->all())
            ->reject(fn($b) => $fromJson->has($b->slug));

        return $fromJson->merge($fromDb);
    }

    /**
     * Save BREAD configuration into JSON storage.
     *
     * @param Bread $bread
     * @return void
     */
    public function save(Bread $bread): void
    {
        $this->json->save($bread);
    }

    /**
     * Delete BREAD configuration from both JSON and Database.
     *
     * @param string $slug
     * @return void
     */
    public function delete(string $slug): void
    {
        $this->json->delete($slug);
        $this->database->delete($slug);
    }

    /**
     * Export all database BREAD configurations to JSON.
     *
     * @return void
     */
    public function exportAllToJson(): void
    {
        $dbBreads = $this->database->all();
        foreach ($dbBreads as $bread) {
            $this->json->save($bread);
        }
    }

    /**
     * Import all JSON BREAD configurations to database (Recovery).
     *
     * @return void
     */
    public function importAllToDatabase(): void
    {
        $jsonBreads = $this->json->all();
        foreach ($jsonBreads as $bread) {
            $this->database->save($bread);
        }
    }
}
