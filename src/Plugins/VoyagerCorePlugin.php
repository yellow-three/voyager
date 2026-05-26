<?php

namespace YellowThree\Voyager\Plugins;

use YellowThree\Voyager\Plugins\Contracts\FormfieldPlugin;
use YellowThree\Voyager\Plugins\Contracts\MenuPlugin;
use YellowThree\Voyager\Plugins\Contracts\WidgetPlugin;
use YellowThree\Voyager\FormFields\CheckboxHandler;
use YellowThree\Voyager\FormFields\CodeEditorHandler;
use YellowThree\Voyager\FormFields\ColorHandler;
use YellowThree\Voyager\FormFields\CoordinatesHandler;
use YellowThree\Voyager\FormFields\DateHandler;
use YellowThree\Voyager\FormFields\FileHandler;
use YellowThree\Voyager\FormFields\HiddenHandler;
use YellowThree\Voyager\FormFields\ImageHandler;
use YellowThree\Voyager\FormFields\MarkdownEditorHandler;
use YellowThree\Voyager\FormFields\MediaPickerHandler;
use YellowThree\Voyager\FormFields\MultipleCheckboxHandler;
use YellowThree\Voyager\FormFields\MultipleImagesHandler;
use YellowThree\Voyager\FormFields\NumberHandler;
use YellowThree\Voyager\FormFields\PasswordHandler;
use YellowThree\Voyager\FormFields\RadioBtnHandler;
use YellowThree\Voyager\FormFields\RichTextBoxHandler;
use YellowThree\Voyager\FormFields\SelectDropdownHandler;
use YellowThree\Voyager\FormFields\SelectMultipleHandler;
use YellowThree\Voyager\FormFields\TextAreaHandler;
use YellowThree\Voyager\FormFields\TextHandler;
use YellowThree\Voyager\FormFields\TimeHandler;
use YellowThree\Voyager\FormFields\TimestampHandler;

class VoyagerCorePlugin extends BasePlugin implements FormfieldPlugin, MenuPlugin, WidgetPlugin
{
    public function name(): string
    {
        return 'voyager-core';
    }

    public function version(): string
    {
        return '3.0.0';
    }

    public function label(): string
    {
        return 'Voyager Core';
    }

    public function description(): string
    {
        return 'Core administration panel functionalities, system form fields and default views.';
    }

    public function author(): string
    {
        return 'Yellow Three Team';
    }

    /**
     * Get core FormField handlers.
     *
     * @return string[]
     */
    public function formFields(): array
    {
        return [
            CheckboxHandler::class,
            CodeEditorHandler::class,
            ColorHandler::class,
            CoordinatesHandler::class,
            DateHandler::class,
            FileHandler::class,
            HiddenHandler::class,
            ImageHandler::class,
            MarkdownEditorHandler::class,
            MediaPickerHandler::class,
            MultipleCheckboxHandler::class,
            MultipleImagesHandler::class,
            NumberHandler::class,
            PasswordHandler::class,
            RadioBtnHandler::class,
            RichTextBoxHandler::class,
            SelectDropdownHandler::class,
            SelectMultipleHandler::class,
            TextAreaHandler::class,
            TextHandler::class,
            TimeHandler::class,
            TimestampHandler::class,
        ];
    }

    /**
     * Get core menu items.
     *
     * @return array
     */
    public function menuItems(): array
    {
        return [
            ['title' => 'Dashboard',  'route' => 'voyager.dashboard',    'icon' => 'home'],
            ['title' => 'Media',      'route' => 'voyager.media.index',  'icon' => 'images'],
            ['title' => 'Users',      'route' => 'voyager.users.index',  'icon' => 'users'],
            ['title' => 'Roles',      'route' => 'voyager.roles.index',  'icon' => 'shield'],
            ['title' => 'Settings',   'route' => 'voyager.settings.index', 'icon' => 'settings'],
            ['title' => 'Database',   'route' => 'voyager.database.index', 'icon' => 'data'],
            ['title' => 'Compass',    'route' => 'voyager.compass.index',  'icon' => 'compass'],
        ];
    }

    /**
     * Get core dashboard widgets.
     *
     * @return array
     */
    public function widgets(): array
    {
        return [
            [
                'view' => 'voyager::components.⚡dashboard',
                'data' => [],
                'width' => 'w-full',
            ]
        ];
    }
}
