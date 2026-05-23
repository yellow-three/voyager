<?php

namespace YellowThree\Voyager\Bread\Sources;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use YellowThree\Voyager\Bread\Bread;
use YellowThree\Voyager\Facades\Voyager;

class DatabaseBreadSource
{
    /**
     * Find a BREAD definition from database tables.
     *
     * @param string $slug
     * @return Bread|null
     */
    public function find(string $slug): ?Bread
    {
        try {
            $dataTypeClass = Voyager::model('DataType');
            if (!$dataTypeClass || !Schema::hasTable((new $dataTypeClass)->getTable())) {
                return null;
            }

            $dataType = $dataTypeClass::where('slug', $slug)->first();

            if (!$dataType) {
                return null;
            }

            // Map data rows to array
            $rows = [];
            foreach ($dataType->rows as $row) {
                $rows[] = [
                    'field' => $row->field,
                    'type' => $row->type,
                    'display_name' => $row->display_name,
                    'required' => (bool)$row->required,
                    'browse' => (bool)$row->browse,
                    'read' => (bool)$row->read,
                    'edit' => (bool)$row->edit,
                    'add' => (bool)$row->add,
                    'delete' => (bool)$row->delete,
                    'details' => is_array($row->details) ? $row->details : (json_decode($row->details, true) ?: []),
                    'order' => $row->order,
                ];
            }

            return new Bread([
                'slug' => $dataType->slug,
                'name' => $dataType->name,
                'display_name_singular' => $dataType->display_name_singular,
                'display_name_plural' => $dataType->display_name_plural,
                'model_name' => $dataType->model_name,
                'controller' => $dataType->controller,
                'icon' => $dataType->icon,
                'generate_permissions' => (bool)$dataType->generate_permissions,
                'server_side' => (bool)$dataType->server_side,
                'details' => is_array($dataType->details) ? $dataType->details : (json_decode($dataType->details, true) ?: []),
                'rows' => $rows,
            ]);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get all BREAD definitions from database storage.
     *
     * @return array<string, Bread>
     */
    public function all(): array
    {
        $breads = [];
        try {
            $dataTypeClass = Voyager::model('DataType');
            if (!$dataTypeClass || !Schema::hasTable((new $dataTypeClass)->getTable())) {
                return [];
            }

            $dataTypes = $dataTypeClass::all();

            foreach ($dataTypes as $dataType) {
                $bread = $this->find($dataType->slug);
                if ($bread) {
                    $breads[$dataType->slug] = $bread;
                }
            }
        } catch (\Exception $e) {}

        return $breads;
    }

    /**
     * Save a BREAD definition into database tables.
     *
     * @param Bread $bread
     * @return void
     */
    public function save(Bread $bread): void
    {
        try {
            $dataTypeClass = Voyager::model('DataType');
            if (!$dataTypeClass) {
                return;
            }

            $dataType = $dataTypeClass::updateOrCreate(
                ['slug' => $bread->slug],
                [
                    'name' => $bread->name,
                    'display_name_singular' => $bread->display_name_singular,
                    'display_name_plural' => $bread->display_name_plural,
                    'model_name' => $bread->model_name,
                    'controller' => $bread->controller,
                    'icon' => $bread->icon,
                    'generate_permissions' => $bread->generate_permissions,
                    'server_side' => $bread->server_side,
                    'details' => json_encode($bread->details),
                ]
            );

            // Sync rows
            $dataRowClass = Voyager::model('DataRow');
            if ($dataRowClass) {
                // Delete existing rows first
                $dataType->rows()->delete();

                foreach ($bread->rows as $row) {
                    $dataType->rows()->create([
                        'field' => $row['field'],
                        'type' => $row['type'],
                        'display_name' => $row['display_name'],
                        'required' => $row['required'],
                        'browse' => $row['browse'],
                        'read' => $row['read'],
                        'edit' => $row['edit'],
                        'add' => $row['add'],
                        'delete' => $row['delete'],
                        'details' => json_encode($row['details']),
                        'order' => $row['order'],
                    ]);
                }
            }
        } catch (\Exception $e) {}
    }

    /**
     * Delete BREAD from database.
     *
     * @param string $slug
     * @return void
     */
    public function delete(string $slug): void
    {
        try {
            $dataTypeClass = Voyager::model('DataType');
            if (!$dataTypeClass) {
                return;
            }

            $dataType = $dataTypeClass::where('slug', $slug)->first();
            if ($dataType) {
                $dataType->rows()->delete();
                $dataType->delete();
            }
        } catch (\Exception $e) {}
    }
}
