<?php

namespace YellowThree\Voyager\Bread;

class Bread
{
    public string $slug;
    public string $name;
    public string $display_name_singular;
    public string $display_name_plural;
    public ?string $model_name = null;
    public ?string $controller = null;
    public ?string $icon = null;
    public bool $generate_permissions = true;
    public bool $server_side = false;
    public array $details = [];
    
    /**
     * Columns rows list.
     *
     * @var array
     */
    public array $rows = [];

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Convert the instance to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'display_name_singular' => $this->display_name_singular,
            'display_name_plural' => $this->display_name_plural,
            'model_name' => $this->model_name,
            'controller' => $this->controller,
            'icon' => $this->icon,
            'generate_permissions' => $this->generate_permissions,
            'server_side' => $this->server_side,
            'details' => $this->details,
            'rows' => $this->rows,
        ];
    }
}
