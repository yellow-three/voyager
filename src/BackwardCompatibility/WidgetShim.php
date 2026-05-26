<?php

namespace YellowThree\Voyager\BackwardCompatibility;

class WidgetShim
{
    /**
     * Legacy widget runner proxy.
     *
     * @param string $widget
     * @param array $params
     * @return string
     */
    public static function run(string $widget, array $params = []): string
    {
        trigger_error(
            "Widget::run() is deprecated in Voyager v3. Use Livewire components instead.",
            E_USER_DEPRECATED
        );

        return '';
    }
}
